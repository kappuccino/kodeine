/**
 * Very simple basic rule set
 *
 * Allows
 *    <i>, <em>, <b>, <strong>, <p>, <div>, <a href="http://foo"></a>, <br>, <span>, <ol>, <ul>, <li>
 *
 * Pour des configurations avancées, voir https://github.com/xing/wysihtml5/blob/master/parser_rules/advanced.js
 *
 */
var wysihtml5ParserRules = {
	tags: {
		strong: {},
		b:      {},
		i:      {},
		em:     {},
		br:     {},
		p:      {},
		div:    {},
		span:   1,
		ul:     {},
		ol:     {},
		li:     {},
		a:      {
			set_attributes: {
				target: "_blank",
				rel:    "nofollow"
			},
			check_attributes: {
				href:   "url" // important to avoid XSS
			}
		}
	}
};