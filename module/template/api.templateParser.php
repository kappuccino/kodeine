<?php

class templatePaster
{
	function template(){
		$this->env = array(
			'kap' => 'kappuccino'
		);
	}

  	function get_pagetitle(){
		return "toto";
	}

    /**
     * Parse for conditional tags
     *
     * @param  string $input
     * @return string
     */
    function parse_conditions($input)
    {
        $matches = preg_split('/<roundcube:(if|elseif|else|endif)\s+([^>]+)>/is', $input, 2, PREG_SPLIT_DELIM_CAPTURE);
        if ($matches && count($matches) == 4) {
            if (preg_match('/^(else|endif)$/i', $matches[1])) {
                return $matches[0] . $this->parse_conditions($matches[3]);
            }
            $attrib = parse_attrib_string($matches[2]);
            if (isset($attrib['condition'])) {
                $condmet = $this->check_condition($attrib['condition']);
                $submatches = preg_split('/<roundcube:(elseif|else|endif)\s+([^>]+)>/is', $matches[3], 2, PREG_SPLIT_DELIM_CAPTURE);
                if ($condmet) {
                    $result = $submatches[0];
                    $result.= ($submatches[1] != 'endif' ? preg_replace('/.*<roundcube:endif\s+[^>]+>/Uis', '', $submatches[3], 1) : $submatches[3]);
                }
                else {
                    $result = "<roundcube:$submatches[1] $submatches[2]>" . $submatches[3];
                }
                return $matches[0] . $this->parse_conditions($result);
            }
            raise_error(array(
                'code' => 500,
                'type' => 'php',
                'line' => __LINE__,
                'file' => __FILE__,
                'message' => "Unable to parse conditional tag " . $matches[2]
            ), true, false);
        }
        return $input;
    }


    /**
     * Determines if a given condition is met
     *
     * @todo   Get rid off eval() once I understand what this does.
     * @todo   Extend this to allow real conditions, not just "set"
     * @param  string Condition statement
     * @return boolean True if condition is met, False if not
     */
    function check_condition($condition)
    {
            return eval("return (".$this->parse_expression($condition).");");
    }


    /**
     * Parses expression and replaces variables
     *
     * @param  string Expression statement
     * @return string Expression statement
     */
    function parse_expression($expression)
    {
        return preg_replace(
            array(
                '/session:([a-z0-9_]+)/i',
                '/config:([a-z0-9_]+)(:([a-z0-9_]+))?/i',
                '/env:([a-z0-9_]+)/i',
                '/request:([a-z0-9_]+)/i',
                '/cookie:([a-z0-9_]+)/i',
            ),
            array(
                "\$_SESSION['\\1']",
                "\$this->app->config->get('\\1',get_boolean('\\3'))",
                "\$this->env['\\1']",
                "get_input_value('\\1', RCUBE_INPUT_GPC)",
                "\$_COOKIE['\\1']",
                
            ),
            $expression);
    }


    /**
     * Search for special tags in input and replace them
     * with the appropriate content
     *
     * @param  string Input string to parse
     * @return string Altered input string
     * @todo   Use DOM-parser to traverse template HTML
     * @todo   Maybe a cache.
     */
    function parse_xml($input)
    {
        return preg_replace_callback('/<roundcube:([-_a-z]+)\s+([^>]+)>/Ui', array($this, 'xml_command_callback'), $input);
    }


    /**
     * This is a callback function for preg_replace_callback (see #1485286)
     * It's only purpose is to reconfigure parameters for xml_command, so that the signature isn't disturbed
     */
    function xml_command_callback($matches)
    {
        $str_attrib = isset($matches[2]) ? $matches[2] : '';
        $add_attrib = isset($matches[3]) ? $matches[3] : array();

        $command = $matches[1];
        //matches[0] is the entire matched portion of the string

        return $this->xml_command($command, $str_attrib, $add_attrib);
    }


    /**
     * Convert a xml command tag into real content
     *
     * @param  string Tag command: object,button,label, etc.
     * @param  string Attribute string
     * @return string Tag/Object content
     */
    function xml_command($command, $str_attrib, $add_attrib = array())
    {
        $command = strtolower($command);
        $attrib  = parse_attrib_string($str_attrib) + $add_attrib;

        // empty output if required condition is not met
        if (!empty($attrib['condition']) && !$this->check_condition($attrib['condition'])) {
            return '';
        }
        
 #       echo $command."<br />";
  #      print_r($attrib);

        // execute command
        switch ($command) {
            // return a button
            case 'button':
                if ($attrib['name'] || $attrib['command']) {
                 #   return $this->button($attrib);
                }
                break;

            // show a label
            case 'label':
                if ($attrib['name'] || $attrib['command']) {
                 #   return Q(rcube_label($attrib + array('vars' => array('product' => $this->config['product_name']))));
                }
                break;

            // include a file
            case 'include':
                $path = realpath($this->config['skin_path'].$attrib['file']);
                if (is_readable($path)) {
                    if ($this->config['skin_include_php']) {
                        $incl = $this->include_php($path);
                    }
                    else {
		        $incl = file_get_contents($path);
		    }
                    return $this->parse_xml($incl);
                }
                break;

            case 'plugin.include':
                //rcube::tfk_debug(var_export($this->config['skin_path'], true));
                $path = realpath($this->config['skin_path'].$attrib['file']);
                if (!$path) {
                    //rcube::tfk_debug("Does not exist:");
                    //rcube::tfk_debug($this->config['skin_path']);
                    //rcube::tfk_debug($attrib['file']);
                    //rcube::tfk_debug($path);
                }
                $incl = file_get_contents($path);
                if ($incl) {
                    return $this->parse_xml($incl);
                }
                break;

            // return code for a specific application object
            case 'object':
                $object = strtolower($attrib['name']);

                // we are calling a class/method
                if (($handler = $this->object_handlers[$object]) && is_array($handler)) {
                    if ((is_object($handler[0]) && method_exists($handler[0], $handler[1])) ||
                    (is_string($handler[0]) && class_exists($handler[0])))
                    return call_user_func($handler, $attrib);
                }
                else if (function_exists($handler)) {
                    // execute object handler function
                    return call_user_func($handler, $attrib);
                }

                if ($object=='productname') {
                    $name = !empty($this->config['product_name']) ? $this->config['product_name'] : 'RoundCube Webmail';
                    return Q($name);
                }
                if ($object=='version') {
                    $ver = (string)RCMAIL_VERSION;
                    if (is_file(INSTALL_PATH . '.svn/entries')) {
                        if (preg_match('/Revision:\s(\d+)/', @shell_exec('svn info'), $regs))
                          $ver .= ' [SVN r'.$regs[1].']';
                    }
                    return $ver;
                }
                if ($object=='steptitle') {
                  return Q($this->get_pagetitle());
                }
                if ($object=='pagetitle') {
                    $title = !empty($this->config['product_name']) ? $this->config['product_name'].' :: ' : '';
                    $title .= $this->get_pagetitle();
                    return Q($title);
                }
                break;

            // return code for a specified eval expression
            case 'exp':
        		$value = $this->parse_expression($attrib['expression']);
                return eval("return Q($value);");
            
            // return variable
            case 'var':
                $var = explode(':', $attrib['name']);
                $name = $var[1];
                $value = '';

                switch ($var[0]) {
                    case 'env':
                    	#echo $command."<br />";
                    	#echo $name."<br />";
                    	
                        $value = $this->env[$name];
                        break;
                    case 'config':
                        $value = $this->config[$name];
                        if (is_array($value) && $value[$_SESSION['imap_host']]) {
                            $value = $value[$_SESSION['imap_host']];
                        }
                        break;
                    case 'request':
                        $value = get_input_value($name, RCUBE_INPUT_GPC);
                        break;
                    case 'session':
                        $value = $_SESSION[$name];
                        break;
                    case 'cookie':
                        $value = htmlspecialchars($_COOKIE[$name]);
                        break;
                }

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
				
				echo $value;
                return Q($value);
                break;
        }
        return '';
    }

    /**
     * Include a specific file and return it's contents
     *
     * @param string File path
     * @return string Contents of the processed file
     */
    function include_php($file)
    {
        ob_start();
        include $file;
        $out = ob_get_contents();
        ob_end_clean();

        return $out;
    }
   
}  // end class rcube_template


