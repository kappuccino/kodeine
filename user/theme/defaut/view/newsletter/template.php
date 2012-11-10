<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
    <headers>
        <!-- This is a simple example template that you can edit to create your own custom templates -->
        <meta http-equiv="Content-Type" content="text/html; ">
        <!-- Facebook sharing information tags -->
        <meta property="og:title" content="*|MC:SUBJECT|*">
        
        <title>*|MC:SUBJECT|*</title>
        <style type="text/css">
        /* NOTE: CSS should be inlined to avoid having it stripped in certain email clients like GMail. 
        MailChimp automatically inlines CSS for you or you can use this tool: http://beaker.mailchimp.com/inline-css. */
        
            /* Client-specific Styles */
            #outlook a{padding:0;} /* Force Outlook to provide a "view in browser" button. */
            body{width:100% !important;} /* Force Hotmail to display emails at full width */
            body{-webkit-text-size-adjust:none;} /* Prevent Webkit platforms from changing default text sizes. */

            /* Reset Styles */
            body{margin:0; padding:0;}
            img{border:none; font-size:14px; font-weight:bold; height:auto; line-height:100%; outline:none; text-decoration:none; text-transform:capitalize;}
            #backgroundTable{height:100% !important; margin:0; padding:0; width:100% !important;}

            /* Template Styles */

            /* /\/\/\/\/\/\/\/\/\/\ STANDARD STYLING: COMMON PAGE ELEMENTS /\/\/\/\/\/\/\/\/\/\ */

            /**
            * @tab Page
            * @section background color
            * @tip Set the background color for your email. You may want to choose one that matches your company's branding.
            * @theme page
            */
            body, .backgroundTable{
                /*@editable*/ background-color:#FFFFFF;
            }

            /**
            * @tab Page
            * @section email border
            * @tip Set the border for your email.
            */
            #templateContainer{
                /*@editable border: 1px solid #DDDDDD;*/
            }

            /**
            * @tab Page
            * @section heading 1
            * @tip Set the styling for all first-level headings in your emails. These should be the largest of your headings.
            * @theme heading1
            */
            h1, .h1{
                /*@editable*/ color:#222222;
                display:block;
                /*@editable*/ font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;
                /*@editable*/ font-size:34px;
                /*@editable*/ font-weight:bold;
                /*@editable*/ line-height:100%;
                /*margin-bottom:10px;*/
                /*@editable*/ text-align:left;
            }

            /**
            * @tab Page
            * @section heading 2
            * @tip Set the styling for all second-level headings in your emails.
            * @theme heading2
            */
            h2, .h2{
                /*@editable*/ color:#222222;
                display:block;
                /*@editable*/ font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;
                /*@editable*/ font-size:30px;
                /*@editable*/ font-weight:bold;
                /*@editable*/ line-height:100%;
                margin-bottom:10px;
                /*@editable*/ text-align:left;
            }

            /**
            * @tab Page
            * @section heading 3
            * @tip Set the styling for all third-level headings in your emails.
            * @theme heading3
            */
            h3, .h3{
                /*@editable*/ color:#222222;
                display:block;
                /*@editable*/ font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;
                /*@editable*/ font-size:26px;
                /*@editable*/ font-weight:bold;
                /*@editable*/ line-height:100%;
                margin-bottom:10px;
                /*@editable*/ text-align:left;
            }

            /**
            * @tab Page
            * @section heading 4
            * @tip Set the styling for all fourth-level headings in your emails. These should be the smallest of your headings.
            * @theme heading4
            */
            h4, .h4{
                /*@editable*/ color:#222222;
                display:block;
                /*@editable*/ font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;
                /*@editable*/ font-size:22px;
                /*@editable*/ font-weight:bold;
                /*@editable*/ line-height:100%;
                /*margin-bottom:10px;*/
                /*@editable*/ text-align:left;
            }

            /* /\/\/\/\/\/\/\/\/\/\ STANDARD STYLING: PREHEADER /\/\/\/\/\/\/\/\/\/\ */

            /**
            * @tab Header
            * @section preheader style
            * @tip Set the background color for your email's preheader area.
            * @theme page
            */
            #templatePreheader{
                /*@editable*/ background-color:#FFFFFF;
            }

            /**
            * @tab Header
            * @section preheader text
            * @tip Set the styling for your email's preheader text. Choose a size and color that is easy to read.
            */
            .preheaderContent div{
                /*@editable*/ color:#505050;
                /*@editable*/ font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;
                /*@editable*/ font-size:10px;
                /*@editable*/ line-height:100%;
                /*@editable*/ text-align:left;
            }

            /**
            * @tab Header
            * @section preheader link
            * @tip Set the styling for your email's preheader links. Choose a color that helps them stand out from your text.
            */
            .preheaderContent div a:link, .preheaderContent div a:visited{
                /*@editable*/ color:#336699;
                /*@editable*/ font-weight:normal;
                /*@editable*/ text-decoration:underline;
            }
            
            .preheaderContent div img{
                height:auto;
                max-width:468px;
            }

            /* /\/\/\/\/\/\/\/\/\/\ STANDARD STYLING: HEADER /\/\/\/\/\/\/\/\/\/\ */

            /**
            * @tab Header
            * @section header style
            * @tip Set the background color and border for your email's header area.
            * @theme header
            */
            #templateHeader{
                /*@editable*/ background-color:#FFFFFF;
                /*@editable*/ border-bottom:0;
            }

            /**
            * @tab Header
            * @section header text
            * @tip Set the styling for your email's header text. Choose a size and color that is easy to read.
            */
            /**
            * @tab Header
            * @section header text
            * @tip Set the styling for your email's header text. Choose a size and color that is easy to read.
            */
            .headerContent{
                /*@editable*/ color:#222222;
                /*@editable*/ font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;
                /*@editable*/ font-size:34px;
                /*@editable*/ font-weight:bold;
                /*@editable*/ line-height:100%;
                /*@editable*/ padding:0;
                /*@editable*/ text-align:center;
                /*@editable*/ vertical-align:middle;
            }

            /**
            * @tab Header
            * @section header link
            * @tip Set the styling for your email's header links. Choose a color that helps them stand out from your text.
            */
            .headerContent a:link, .headerContent a:visited{
                /*@editable*/ color:#336699;
                /*@editable*/ font-weight:normal;
                /*@editable*/ text-decoration:underline;
            }

            #headerImage{
                height:auto;
                max-width:468px !important;
            }

            /* /\/\/\/\/\/\/\/\/\/\ STANDARD STYLING: MAIN BODY /\/\/\/\/\/\/\/\/\/\ */

            /**
            * @tab Body
            * @section body style
            * @tip Set the background color for your email's body area.
            */
            #templateContainer, .bodyContent{
                /*@editable*/ background-color:#FFFFFF;
            }

            /**
            * @tab Body
            * @section body text
            * @tip Set the styling for your email's main content text. Choose a size and color that is easy to read.
            * @theme main
            */
            .bodyContent div{
                /*@editable*/ color:#505050;
                /*@editable*/ font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;
                /*@editable*/ font-size:14px;
                /*@editable*/ line-height:150%;
                /*@editable*/ text-align:left;
            }

            /**
            * @tab Body
            * @section body link
            * @tip Set the styling for your email's main content links. Choose a color that helps them stand out from your text.
            */
            a, .bodyContent div a:link, .bodyContent div a:visited{
                /*@editable*/ color:#222222;
                /*@editable*/ font-weight:normal;
                /*@editable*/ text-decoration:none;
            }
            a:hover, .bodyContent div a:hover{
                /*@editable*/ color:#D20000;
                /*@editable*/ font-weight:normal;
                /*@editable*/ text-decoration:none;
            }

            .bodyContent img{
                display:inline;
                /*margin-bottom:10px;*/
            }

            /* /\/\/\/\/\/\/\/\/\/\ STANDARD STYLING: FOOTER /\/\/\/\/\/\/\/\/\/\ */

            /**
            * @tab Footer
            * @section footer style
            * @tip Set the background color and top border for your email's footer area.
            * @theme footer
            */
            #templateFooter{
                /*@editable*/ background-color:#FFFFFF;
                /*@editable*/ border-top:0;
            }

            /**
            * @tab Footer
            * @section footer text
            * @tip Set the styling for your email's footer text. Choose a size and color that is easy to read.
            * @theme footer
            */
            .footerContent div{
                /*@editable*/ color:#707070;
                /*@editable*/ font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;
                /*@editable*/ font-size:12px;
                /*@editable*/ line-height:125%;
                /*@editable*/ text-align:left;
            }

            /**
            * @tab Footer
            * @section footer link
            * @tip Set the styling for your email's footer links. Choose a color that helps them stand out from your text.
            */
            .footerContent div a:link, .footerContent div a:visited{
                /*@editable*/ color:#336699;
                /*@editable*/ font-weight:normal;
                /*@editable*/ text-decoration:underline;
            }

            .footerContent img{
                display:inline;
            }

            /**
            * @tab Footer
            * @section social bar style
            * @tip Set the background color and border for your email's footer social bar.
            */
            #social{
                /*@editable background-color:#FFFFFF;*/
                /*@editable border:1px solid #F5F5F5;*/
            }

            /**
            * @tab Footer
            * @section social bar style
            * @tip Set the background color and border for your email's footer social bar.
            */
            #social div{
                /*@editable*/ text-align:center;
            }

            /**
            * @tab Footer
            * @section utility bar style
            * @tip Set the background color and border for your email's footer utility bar.
            */
            #utility{
                /*@editable background-color:#FFFFFF;*/
                /*@editable border-top:1px solid #F5F5F5;*/
            }

            /**
            * @tab Footer
            * @section utility bar style
            * @tip Set the background color and border for your email's footer utility bar.
            */
            #utility div{
                /*@editable*/ text-align:center;
            }

            #monkeyRewards img{
                max-width:160px;
            }
        </style>
    </headers>
    </head>
    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="-webkit-text-size-adjust: none;margin: 0;padding: 0;background-color: #FFFFFF;width: 100% !important;">
 <container>
        <center>
            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="backgroundTable" style="margin: 0;padding: 0;height: 100% !important;width: 100% !important;">
                <tr>
                    <td align="center" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="468" id="templateContainer" style="margin-top: 10px;background-color: #ffffff;">
                            <tr>
                                <td align="center" valign="top">
                                    <!-- // Begin Template Header \\ -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="468" id="templateHeader" style="background-color: #FFFFFF;border-bottom: 0;">
                                        <tr>
                                            <td class="headerContent" style="color: #222222;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 9px;font-weight: normal;line-height: 100%;padding: 0;text-align: left;vertical-align: middle;">
                                                <div style="border-top: 3px solid #222;width: 100%"></div>
                                                <img src="/media/newsletter/img/NL-titre.png" style="max-width: 468px;border: none;font-size: 14px;font-weight: bold;height: auto;line-height: 100%;outline: none;text-decoration: none;text-transform: capitalize;" id="headerImage campaign-icon" mc:label="header_image" mc:edit="header_image" mc:allowdesigner mc:allowtext>
                                                <div style="border-top: 3px solid #222;width: 100%"></div>
                                                
                                                <!-- // Begin Module: Standard Content \\ -->
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 10px;border-bottom: 1px solid #BEBEBE;" >
                                                    <tr>
                                                        <td valign="top" width="270">                                                            
                                                            LA NEWSLETTER DE CONTRÔLES ESSAIS MESURES.FR
                                                        </td>
                                                        <td valign="top" align="right" width="196"> 
                                                            
                                                            <repeaters data-ref="date">
                                                                <repeater data-ref="date" data-name="Date">                                                           
                                                                    <layout data-ref="date" data-name="Date">
                                                                        <span class="txt" style="font-size:9px;line-height:14px; font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;">
                                                                            <span class="item" data-ref="date" data-name="Date" data-type="singleline">30 NOVEMBRE 2012</span> 
                                                                        </span>  
                                                                    </layout> 
                                                                </repeater> 
                                                            </repeaters> <img src="/media/newsletter/img/NL-icon.png" width="28" height="12"> 
                                                            <repeaters data-ref="numero">
                                                                <repeater data-ref="numero" data-name="Numéro">                                                           
                                                                    <layout data-ref="numero" data-name="Numéro">
                                                                        <span class="txt" style="font-size:9px;line-height:14px; font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;">
                                                                            <span class="item" data-ref="numero" data-name="Numéro" data-type="singleline">NUMÉRO 1</span>  
                                                                        </span>  
                                                                    </layout> 
                                                                </repeater> 
                                                            </repeaters>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- // End Module: Standard Content \\ -->
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- // End Template Header \\ -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top">
                                    <!-- // Begin Template Body \\ -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="468" id="templateBody">
                                        <tr>
                                            <td valign="top" class="bodyContent" style="background-color: #FFFFFF;">
                                
                                                <!-- // Begin Module: Standard Content \\ -->
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td valign="top">
                                                            
                                                            <!-- // Ad 468x60 \\ -->
                                                            <div style="margin-top: 15px;margin-bottom: 15px; line-height: 0;">
                                                                <!--<a href=""><img src="/media/pub/gauche/468x60.jpg"></a>-->
                                                            </div>
                                                            <div style="border-top: 1px solid #bebebe;width: 100%"></div>
                                               
                                                            <!-- // End Ad 468x60 \\ -->
                                                            
                                                                
                                                            <repeaters data-ref="actualites" data-multiple="1">
                                                                <repeater data-type="content" data-ref="actualites" data-name="Actualités">                                                        
                                                                    <layout data-ref="actualites" data-name="Actualité">
                                                                        
                                                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 0px;" >
                                                                <tr>
                                                                    <td valign="top" width="100" style="padding-top:10px;padding-bottom: 10px;border-bottom: 1px solid #BEBEBE;">                                                            
                                                                        <img src="http://devcem.kappuccino.org/media/.cache/actualites/9/9/2/3827354719-992.jpg" width="90">
                                                                    </td>
                                                                    <td valign="top" width="368" style="padding-top:10px;padding-bottom: 10px;border-bottom: 1px solid #BEBEBE;">                                                            
                                                                        <div style="font-size: 11px;">
                                                                            <a href="/mesures"><img src="/media/newsletter/img/NL-mesures.png"></a> 14 juillet 2012
                                                                        </div>
                                                                        <span class="h1" style="color: #222222;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 17px;font-weight: bold;line-height: 22px;text-align: left;">
                                                                            <span class="item"  data-fieldKey="field.titreActu" data-url="/designer/get-titre" data-ref="titreActu" data-name="Titre" data-type="singleline">
                                                                                Scanner et palpeurs contact se rejoignent
                                                                            </span>
                                                                        </span>
                                                                        <span class="txt" style="font-size:12px;line-height:16px; font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;">
                                                                            Le nouveau scanner laser numérique LC15Dx de Nikon Metrology a franchi une nouvelle étape...
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            </table>

                                                                    </layout> 
                                                                </repeater> 
                                                            </repeaters>
                                                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 0px;" >
                                                                <tr>
                                                                    <td valign="top" width="100" style="padding-top:10px;padding-bottom: 10px;border-bottom: 1px solid #BEBEBE;">                                                            
                                                                        <img src="http://devcem.kappuccino.org/media/.cache/actualites/9/9/2/3827354719-992.jpg" width="90">
                                                                    </td>
                                                                    <td valign="top" width="368" style="padding-top:10px;padding-bottom: 10px;border-bottom: 1px solid #BEBEBE;">                                                            
                                                                        <div style="font-size: 11px;">
                                                                            <a href="/optique"><img src="/media/newsletter/img/NL-optique.png"></a> 18 juillet 2012
                                                                        </div>
                                                                        <span class="h1" style="color: #222222;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 17px;font-weight: bold;line-height: 22px;text-align: left;">
                                                                            Scanner et palpeurs contact se rejoignent
                                                                        </span>
                                                                        <span class="txt" style="font-size:12px;line-height:16px; font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;">
                                                                            Le nouveau scanner laser numérique LC15Dx de Nikon Metrology a franchi une nouvelle étape...
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td valign="top" width="100" style="padding-top:10px;padding-bottom: 10px;border-bottom: 1px solid #BEBEBE;">                                                            
                                                                        <img src="http://devcem.kappuccino.org/media/.cache/actualites/9/9/2/3827354719-992.jpg" width="90">
                                                                    </td>
                                                                    <td valign="top" width="368" style="padding-top:10px;padding-bottom: 10px;border-bottom: 1px solid #BEBEBE;">                                                            
                                                                        <div style="font-size: 11px;">
                                                                            <a href="/cnd"><img src="/media/newsletter/img/NL-cnd.png"></a> 14 juillet 2012
                                                                        </div>
                                                                        <span class="h1" style="color: #222222;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 17px;font-weight: bold;line-height: 22px;text-align: left;">
                                                                            Scanner et palpeurs contact se rejoignent
                                                                        </span>
                                                                        <span class="txt" style="font-size:12px;line-height:16px; font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;">
                                                                            Le nouveau scanner laser numérique LC15Dx de Nikon Metrology a franchi une nouvelle étape...
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td valign="top" width="100" style="padding-top:10px;padding-bottom: 10px;border-bottom: 1px solid #BEBEBE;">                                                            
                                                                        <img src="http://devcem.kappuccino.org/media/.cache/actualites/9/9/2/3827354719-992.jpg" width="90">
                                                                    </td>
                                                                    <td valign="top" width="368" style="padding-top:10px;padding-bottom: 10px;border-bottom: 1px solid #BEBEBE;">                                                            
                                                                        <div style="font-size: 11px;">
                                                                            <a href="/automatismes"><img src="/media/newsletter/img/NL-automatismes.png"></a> 14 juillet 2012
                                                                        </div>
                                                                        <span class="h1" style="color: #222222;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 17px;font-weight: bold;line-height: 22px;text-align: left;">
                                                                            Scanner et palpeurs contact se rejoignent
                                                                        </span>
                                                                        <span class="txt" style="font-size:12px;line-height:16px; font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;">
                                                                            Le nouveau scanner laser numérique LC15Dx de Nikon Metrology a franchi une nouvelle étape...
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td valign="top" width="100" style="padding-top:10px;padding-bottom: 10px;border-bottom: 1px solid #BEBEBE;">                                                            
                                                                        <img src="http://devcem.kappuccino.org/media/.cache/actualites/9/9/2/3827354719-992.jpg" width="90">
                                                                    </td>
                                                                    <td valign="top" width="368" style="padding-top:10px;padding-bottom: 10px;border-bottom: 1px solid #BEBEBE;">                                                            
                                                                        <div style="font-size: 11px;">
                                                                            <a href="/management"><img src="/media/newsletter/img/NL-management.png"></a> 14 juillet 2012
                                                                        </div>
                                                                        <span class="h1" style="color: #222222;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 17px;font-weight: bold;line-height: 22px;text-align: left;">
                                                                            Scanner et palpeurs contact se rejoignent
                                                                        </span>
                                                                        <span class="txt" style="font-size:12px;line-height:16px; font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;">
                                                                            Le nouveau scanner laser numérique LC15Dx de Nikon Metrology a franchi une nouvelle étape...
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2" style="padding-top:10px;padding-bottom: 0px;">                                                            
                                                                        <a href="/agenda/"><img src="/media/newsletter/img/NL-agenda.jpg"></a>
                                                                       
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2" style="padding-top:10px;padding-bottom: 10px;border-bottom: 1px solid #BEBEBE;">                                                            
                                                                        <a href="/agenda/">
                                                                            <span class="luc11" style="color: #222222;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 11px;font-weight: bold;line-height: 16px;text-align: left;">
                                                                                Du 17/08/2012 au 19/08/2012
                                                                            </span>
                                                                            <span class="h1" style="color: #222222;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 17px;font-weight: bold;line-height: 22px;text-align: left;">
                                                                                Salon Micronora 2012
                                                                            </span>
                                                                            <span class="txt" style="font-size:12px;line-height:16px; font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;">
                                                                                69009 Lyon
                                                                            </span>
                                                                        </a>
                                                                       
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2" style="padding-top:10px;padding-bottom: 10px; border-bottom: 1px solid #BEBEBE;">                                                            
                                                                        <a href="/agenda/">
                                                                            <span class="luc11" style="color: #222222;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 11px;font-weight: bold;line-height: 16px;text-align: left;">
                                                                                Du 17/08/2012 au 19/08/2012
                                                                            </span>
                                                                            <span class="h1" style="color: #222222;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 17px;font-weight: bold;line-height: 22px;text-align: left;">
                                                                                Salon Micronora 2012
                                                                            </span>
                                                                            <span class="txt" style="font-size:12px;line-height:16px; font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;">
                                                                                69009 Lyon
                                                                            </span>
                                                                        </a>
                                                                       
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2" style="padding-top:10px;padding-bottom: 0px;">                                                            
                                                                        <a href="<?php echo ROOTEMPLOI; ?>"><img src="/media/newsletter/img/NL-emploi.jpg"></a>
                                                                       
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2" style="padding-top:10px;padding-bottom: 10px; border-bottom: 1px solid #BEBEBE;">                                                            
                                                                        <a href="/agenda/">
                                                                            <span class="luc11" style="color: #222222;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 11px;font-weight: bold;line-height: 16px;text-align: left;">
                                                                                19/08/2012
                                                                            </span>
                                                                            <span class="h1" style="color: #222222;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 17px;font-weight: bold;line-height: 22px;text-align: left;">
                                                                                Métrologue H/F
                                                                            </span>
                                                                            <span class="txt" style="font-size:12px;line-height:16px; font-family:'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;">
                                                                                Lyon
                                                                            </span>
                                                                        </a>
                                                                       
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            
                                                            
                                                            <!-- // Ad 468x60 \\ -->
                                                            <div style="border-top: 1px solid #bebebe;width: 100%"></div>
                                                            <div style="margin-top: 15px;margin-bottom: 15px; line-height: 0;">
                                                                <!--<a href=""><img src="/media/pub/gauche/468x60.jpg"></a>-->
                                                            </div>
                                                            <div style="border-top: 3px solid #222;width: 100%;margin-bottom: 30px;"></div>
                                                
                                               
                                                            <!-- // End Ad 468x60 \\ -->
                                                            
                                                            
                                                            <a href="/abonnement"><img src="/media/newsletter/img/NL-revue.png"></a>
                                                                       
                                                            
                                                            
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- // End Module: Standard Content \\ -->
                                                
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- // End Template Body \\ -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top">
                                    <!-- // Begin Template Footer \\ -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="468" id="templateFooter" style="background-color: #FFFFFF;border-top: 0;">
                                        <tr>
                                            <td valign="top" class="footerContent">
                                            
                                                <!-- // Begin Module: Standard Footer \\ -->
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td align="left" style="padding-top:20px;padding-bottom: 10px; text-align: center;">
                                                            <span class="txt" style="color: #6E6E6E;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 10px;font-weight: normal;line-height: 14px;text-align: left;">
                                                                Envoyé à martin.dehalleux@lexitis.fr - Se désinscrire - Transférer<br /><br />
                                                                Vous recevez ce message parce que vous avez choisi de recevoir les informations provenant de Editocom / Mesures and Co / Contrôles Essais Mesures ou de leurs partenaires.
                                                                Conformément à l'article 27 de la loi "informatique et Liberté" du 6 janvier 1978, vous disposez d'un droit d'accès et de rectification des informations vous concernant.
                                                                Ce droit peut s'exercer en nous écrivant. Ce message vous est également adressé au titre des fonctions que vous occupez conformément à l'avis de la CNIL du 17 février 2005.
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center" style="padding-top:30px;padding-bottom: 10px; text-align: center;">
                                                            <a href="" target="_blank">
                                                                <span class="h1" style="color: #222222;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 17px;font-weight: bold;line-height: 22px;text-align: center;">
                                                                    www.controles-essais-mesures.fr
                                                                </span>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td valign="top" style="padding-top:10px;padding-bottom: 10px; border-top: 1px solid #BEBEBE; border-bottom: 1px solid #BEBEBE;">
                                                            <span class="txt" style="color: #6E6E6E;display: inline-block;margin-right: 5px;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 11px;font-weight: normal;line-height: 16px;text-align: left;">
                                                                    Nos partenaires
                                                            </span>
                                                                    <a href="http://www.cfmetrologie.com/">
                                                                        <img src="/media/newsletter/img/part-college.png">
                                                                    </a>
                                                                    <a href="http://www.cofrend.com/">
                                                                        <img src="/media/newsletter/img/part-cofrend.png">
                                                                    </a>
                                                                    <a href="http://www.symop.com/">
                                                                        <img src="/media/newsletter/img/part-symop.png">
                                                                    </a>
                                                                    <a href="http://www.club-cmoi.fr/">
                                                                        <img src="/media/newsletter/img/part-cmoi.png">
                                                                    </a>
                                                                    <a href="http://www.rmvo.com/">
                                                                        <img src="/media/newsletter/img/part-mesures.png">
                                                                    </a>
                                                            
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td valign="top" style="padding-top:10px;padding-bottom: 10px;">
                                                            <div class="txt" style="color: #6E6E6E;display: block;font-family: 'Lucida Grande','Lucida Sans Unicode', Verdana, sans-serif;font-size: 11px;font-weight: normal;line-height: 16px;text-align: left;">
                                                                    <img src="/media/newsletter/img/NL-icon.png" width="40">
                                                                    <a href="/qui-sommes-nous" target="_blank">Qui sommes-nous ?</a>
                                                                     - <a href="/qui-sommes-nous" target="_blank">Mentions légales</a>
                                                                     - &copy; <a href="http://www.editocom.com/" target="_blank">Editocom</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- // End Module: Standard Footer \\ -->
                                            
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- // End Template Footer \\ -->
                                </td>
                            </tr>
                        </table>
                        <br>
                    </td>
                </tr>
            </table>
        </center>

 </container>
</body>
</html>
