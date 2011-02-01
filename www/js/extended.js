/* nominify */
if (!String.prototype.trim) {
    String.prototype.trim = function () {
        return this.replace(/^\s*/, "").replace(/\s*$/, "");
    };
}
if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function(elt) {
        var len = this.length;
        var from = Number(arguments[1]) || 0;
        from = (from < 0)  ? Math.ceil(from)  : Math.floor(from);
        if (from < 0)
            from += len;

        for (; from < len; from++) {
        if (from in this && this[from] === elt)
            return from;
        }
        return -1;
    };
}

RegExp.escape = function(str) {
  var specials = new RegExp("[.*+?|()\\[\\]{}\\\\]", "g"); // .*+?|()[]{}\
  return str.replace(specials, "\\$&");
};

var Caret = {
    cc:'\u2009',
    restore:function() {



        switch(true) {
            case $.browser.msie:
                var range = document.body.createTextRange();
                if(range.findText(this.cc)){
                    range.select();
                    range.text = '';
                }
                break;
            
            case $.browser.opera:
                var sel = window.getSelection();
                var range = window.document.createRange();
                var span = window.document.getElementsByTagName('span')[0];

                range.selectNode(span);
                sel.removeAllRanges();
                sel.addRange(range);
                span.parentNode.removeChild(span);
                break;
           default:
                var result = window.find(this.cc);
                if(result) {
					try {
						window.getSelection().getRangeAt(0).deleteContents();
					} catch(e){}
                }
                break;

        }
    },
    save:function() {
        switch(true) {
            case $.browser.msie:
                document.selection.createRange().text = this.cc;
                break;
            case $.browser.opera:
                var span = document.createElement('span');
                window.getSelection().getRangeAt(0).insertNode(span);
                break;
            default:
				try {
					window.getSelection().getRangeAt(0).insertNode(document.createTextNode(this.cc));
				} catch(e) {}
        }
    },
    insertText:function(text) {
        switch(true) {
            case $.browser.msie:
                document.selection.createRange().text = text + this.cc;
                break;
            default:
                window.getSelection().getRangeAt(0).insertNode(document.createTextNode(text + this.cc));
        }
        this.restore();
    },
    insertNode:function(node) {
        switch(true) {
            case $.browser.msie:
                var dummy = $('<div/>')
                dummy.append(node);
                document.selection.createRange().text = dummy.html() + this.cc;
                break;
            default:
                window.getSelection().getRangeAt(0).insertNode(document.createTextNode(this.cc));
                window.getSelection().getRangeAt(0).insertNode(node);
        }
        this.restore();
    }

};

/*****************************************************************
 * Word HTML Cleaner
 * Copyright (C) 2005 Connor McKay
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
*****************************************************************/
var HTML = {
    //remplacement characters
    rchars: [["Ã±","Ã³", "Ã«", "Ã­", "Ã¬", "Ã®", 'â€ '], ["-", "-", "'", "'", '"', '"', ' ']],

    //html entities translation array
    hents: {'Â°':'&iexcl;',
        'Â¢':'&cent;',
        'Â£':'&pound;',
        'Â§':'&curren;',
        'â€¢':'&yen;',
        'Â¶':'&brvbar;',
        'ÃŸ':'&sect;',
        'Â®':'&uml;',
        'Â©':'&copy;',
        'â„¢':'&ordf;',
        'Â´':'&laquo;',
        'Â¨':'&not;',
        'â‰ ':'&shy;',
        'Ã†':'&reg;',
        'Ã˜':'&macr;',
        'âˆž':'&deg;',
        'Â±':'&plusmn;',
        'â‰¤':'&sup2;',
        'â‰¥':'&sup3;',
        'Â¥':'&acute;',
        'Âµ':'&micro;',
        'âˆ‚':'&para;',
        'âˆ‘':'&middot;',
        'âˆ':'&cedil;',
        'Ï€':'&sup1;',
        'âˆ«':'&ordm;',
        'Âª':'&raquo;',
        'Âº':'&frac14;',
        'Î©':'&frac12;',
        'Ã¦':'&frac34;',
        'Ã¸':'&iquest;',
        'Â¿':'&Agrave;',
        'Â¡':'&Aacute;',
        'Â¬':'&Acirc;',
        'âˆš':'&Atilde;',
        'Æ’':'&Auml;',
        'â‰ˆ':'&Aring;',
        'âˆ†':'&AElig;',
        'Â«':'&Ccedil;',
        'Â»':'&Egrave;',
        'â€¦':'&Eacute;',
        'Â ':'&Ecirc;',
        'Ã€':'&Euml;',
        'Ãƒ':'&Igrave;',
        'Ã•':'&Iacute;',
        'Å’':'&Icirc;',
        'Å“':'&Iuml;',
        'â€“':'&ETH;',
        'â€”':'&Ntilde;',
        'â€œ':'&Ograve;',
        'â€':'&Oacute;',
        'â€˜':'&Ocirc;',
        'â€™':'&Otilde;',
        'Ã·':'&Ouml;',
        'â—Š':'&times;',
        'Ã¿':'&Oslash;',
        'Å¸':'&Ugrave;',
        'â„':'&Uacute;',
        'â‚¬':'&Ucirc;',
        'â€¹':'&Uuml;',
        'â€º':'&Yacute;',
        'ï¬':'&THORN;',
        'ï¬‚':'&szlig;',
        'â€¡':'&agrave;',
        'Â·':'&aacute;',
        'â€š':'&acirc;',
        'â€ž':'&atilde;',
        'â€°':'&auml;',
        'Ã‚':'&aring;',
        'ÃŠ':'&aelig;',
        'Ã':'&ccedil;',
        'Ã‹':'&egrave;',
        'Ãˆ':'&eacute;',
        'Ã':'&ecirc;',
        'ÃŽ':'&euml;',
        'Ã':'&igrave;',
        'ÃŒ':'&iacute;',
        'Ã“':'&icirc;',
        'Ã”':'&iuml;',
        'ï£¿':'&eth;',
        'Ã’':'&ntilde;',
        'Ãš':'&ograve;',
        'Ã›':'&oacute;',
        'Ã™':'&ocirc;',
        'Ä±':'&otilde;',
        'Ë†':'&ouml;',
        'Ëœ':'&divide;',
        'Â¯':'&oslash;',
        'Ë˜':'&ugrave;',
        'Ë™':'&uacute;',
        'Ëš':'&ucirc;',
        'Â¸':'&uuml;',
        'Ë':'&yacute;',
        'Ë›':'&thorn;',
        'Ë‡':'&yuml;',
        '"':'&quot;',
        '<':'&lt;',
        '>':'&gt;'},

    //allowed tags
    tags:['div','span','font', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'h8', 'ul', 'ol', 'li', 'u', 'i', 'b', 'a', 'table', 'tr', 'th', 'td', 'img', 'em', 'strong', 'br'],

    //tags which should be removed when empty
    rempty:['p','span','div','h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'h8', 'ul', 'ol', 'li', 'u', 'i', 'b', 'a', 'table', 'tr', 'em', 'strong'],

    //allowed atributes for tags
    aattr:{
        'a':['href', 'name'],
        'font':['face'],
        'span':['class'],
        'div':['class'],
        'table':['border'],
        'th':['colspan', 'rowspan'],
        'td':['colspan', 'rowspan'],
        'img':['src', 'width', 'height', 'alt']
    },

    //tags who's content should be deleted
    dctags:['head'],

    //Quote characters
    quotes:["'", '"'],

    //tags which are displayed as a block
    btags:['p','div','h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'h8', 'ul', 'ol', 'li', 'table', 'tr', 'th', 'td', 'br'],

    //d = data, o = out, c = character, n = next character
    //in and out variables

    clean:function(source) {
        var o = '';
        var i;
        //Replace all whitespace characters with spaces
        source = source.replace(/(\s|&nbsp;)+/g, ' ');
        //replace weird word characters
        for (i = 0; i < this.rchars[0].length; i++) {
            source = source.replace(new RegExp(this.rchars[0][i], 'g'), this.rchars[1][i]);
        }

        //initialize flags
        //what the next character is expected to be
        var expected = '';
        //tag text
        var tag = '';
        //tag name
        var tagname = '';
        //what type of tag it is, start, end, or single
        var tagtype = 'start';
        //attribute text
        var attribute = '';
        //attribute name
        var attributen = '';
        //if the attribute has had an equals sign
        var attributeequals = false;
        //if attribute has quotes, and what they are
        var attributequotes = '';

        var c = '';
        var n = '';

        /*Parser format:
        The parser is divided into three parts:
        The first section is for when the current type of character is known
        The second is for when it is an unknown character in a tag
        The third is for anything outside of a tag
        */

        //editing pass
        for (i = 0; i < source.length; i++)
        {
            //current character
            c = this.getc(source,i);
            //next character
            n = this.getc(source,i+1);

            //***Section for when the current character is known

            //if the tagname is expected
            if (expected == 'tagname')
            {
                tagname += c.toLowerCase();
                //lookahead for end of tag name
                if (n == ' ' || n == '>' || n == '/')
                {
                    tag += tagname;
                    expected = 'tag';
                }
            }
            //if an attribute name is expected
            else if (expected == 'attributen')
            {
                attributen += c.toLowerCase();
                //lookahead for end of attribute name
                if (n == ' ' || n == '>' || n == '/' || n == '=')
                {
                    attribute += attributen;
                    //check to see if its an attribute without an assigned value
                    //determines whether there is anything but spaces between the attribute name and the next equals sign
                    if (this.endOfAttr(source,i))
                    {
                        //if the attribute is allowed, add it to the output
                        if (this.ae(attributen, this.aattr[tagname]))
                            tag += attribute;

                        attribute = '';
                        attributen = '';
                        attributeequals = false;
                        attributequotes = '';
                    }
                    expected = 'tag';
                }
            }
            //if an attribute value is expected
            else if (expected == 'attributev')
            {
                attribute += c;

                //lookahead for end of value
                if ((c == attributequotes) || ((n == ' ' || n == '/' || n == '>') && !attributequotes))
                {
                    //if the attribute is allowed, add it to the output
                    if (this.ae(attributen, this.aattr[tagname]))
                        tag += attribute;

                    attribute = '';
                    attributen = '';
                    attributeequals = false;
                    attributequotes = '';

                    expected = 'tag';
                }
            }

            //***Section for when the character is unknown but it is inside of a tag

            else if (expected == 'tag')
            {
                //if its a space
                if (c == ' ')
                    tag += c;
                //if its a slash after the tagname, signalling a single tag.
                else if (c == '/' && tagname)
                {
                    tag += c;
                    tagtype = 'single';
                }
                //if its a slash before the tagname, signalling its an end tag
                else if (c == '/')
                {
                    tag += c;
                    tagtype = 'end';
                }
                //if its the end of a tag
                else if (c == '>')
                {
                    tag += c;
                    //if the tag is allowed, add it to the output
                    if (this.ae(tagname, this.tags))
                        o += tag;

                    //if its a start tag
                    if (tagtype == 'start')
                    {
                        //if the tag is supposed to have its contents deleted
                        if (this.ae(tagname, this.dctags))
                        {
                            //if there is an end tag, skip to it in order to delete the tags contents
                            if (-1 != (this.endpos = source.indexOf('</' + tagname, i)))
                            {
                                //have to make it one less because i gets incremented at the end of the loop
                                i = this.endpos-1;
                            }
                            //if there isn't an end tag, then it was probably a non-compliant single tag
                        }
                    }

                    tag = '';
                    tagname = '';
                    tagtype = 'start';
                    expected = '';
                }
                //if its an attribute name
                else if (tagname && !attributen)
                {
                    attributen += c.toLowerCase();
                    expected = 'attributen';
                    //lookahead for end of attribute name, in case its a one character attribute name
                    if (n == ' ' || n == '>' || n == '/' || n == '=')
                    {
                        attribute += attributen;
                        //check to see if its an attribute without an assigned value
                        //determines whether there is anything but spaces between the attribute name and the next equals sign
                        if (this.endOfAttr(source,i))
                        {
                            //if the attribute is allowed, add it to the output
                            if (this.ae(attributen, attributen))
                                tag += attribute;

                            attribute = '';
                            attributen = '';
                            attributeequals = false;
                            attributequotes = '';
                        }
                        expected = 'tag';
                    }
                }
                //if its a start quote for an attribute value
                else if (this.ae(c, this.quotes) && attributeequals)
                {
                    attribute += c;
                    attributequotes = c;
                    expected = 'attributev';
                }
                //if its an attribute value
                else if (attributeequals)
                {
                    attribute += c;
                    expected = 'attributev';

                    //lookahead for end of value, in case its only one character
                    if ((c == attributequotes) || ((n == ' ' || n == '/' || n == '>') && !attributequotes))
                    {
                        //if the attribute is allowed, add it to the output
                        if (this.ae(attributen, attributen))
                            tag += attribute;

                        attribute = '';
                        attributen = '';
                        attributeequals = false;
                        attributequotes = '';

                        expected = 'tag';
                    }
                }
                //if its an attribute equals
                else if (c == '=' && attributen)
                {
                    attribute += c;
                    attributeequals = true;
                }
                //if its the tagname
                else
                {
                    tagname += c.toLowerCase();
                    expected = 'tagname';

                    //lookahead for end of tag name, in case its a one character tag name
                    if (n == ' ' || n == '>' || n == '/')
                    {
                        tag += tagname;
                        expected = 'tag';
                    }
                }
            }
            //if nothing is expected
            else
            {
                //if its the start of a tag
                if (c == '<')
                {
                    tag = c;
                    expected = 'tag';
                }
                //anything else
                else
                    o += this.htmlentities(c);
            }
        }

        //beautifying regexs
        //remove duplicate spaces
        o = o.replace(/\s+/g, ' ');
        //remove unneeded spaces in tags
        o = o.replace(/\s>/g, '>');
        //remove empty tags
        //this loops until there is no change from running the regex
        var remptys = this.rempty.join('|');
        var oo = o;
        while ((o = o.replace(new RegExp("\\s?<(" + remptys + ")>\s*<\\/\\1>", 'gi'), '')) != oo)
            oo = o;
        //make block tags regex string
        var btagss = this.btags.join('|');
        //add newlines after block tags
        o = o.replace(new RegExp("\\s?</(" + btagss+ ")>", 'gi'), "</$1>\n");
        //remove spaces before block tags
        o = o.replace(new RegExp("\\s<(" + btagss + ")", 'gi'), "<$1");

        //fix lists
        o = o.replace(/((<p.*>\s*(&middot;|&#9642;) .*<\/p.*>\n)+)/gi, "<ul>\n$1</ul>\n");//make ul for dot lists
        o = o.replace(/((<p.*>\s*\d+\S*\. .*<\/p.*>\n)+)/gi, "<ol>\n$1</ol>\n");//make ol for numerical lists
        o = o.replace(/((<p.*>\s*[a-z]+\S*\. .*<\/p.*>\n)+)/gi, "<ol style=\"list-style-type: lower-latin;\">\n$1</ol>\n");//make ol for latin lists
        o = o.replace(/<p(.*)>\s*(&middot;|&#9642;|\d+(\S*)\.|[a-z]+\S*\.) (.*)<\/p(.*)>\n/gi, "\t<li$1>$3$4</li$5>\n");//make li

        //extend outer lists around the nesting lists
        o = o.replace(/<\/(ul|ol|ol style="list-style-type: lower-latin;")>\n(<(?:ul|ol|ol style="list-style-type: lower-latin;")>[\s\S]*<\/(?:ul|ol|ol style="list-style-type: lower-latin;")>)\n(?!<(ul|ol|ol style="list-style-type: lower-latin;")>)/g, "</$1>\n$2\n<$1>\n</$1>\n");

        //nesting lists
        o = o.replace(/<\/li>\s+<\/ol>\s+<ul>([\s\S]*?)<\/ul>\s+<ol>/g, "\n<ul>$1</ul></li>");//ul in ol
        o = o.replace(/<\/li>\s+<\/ol>\s+<ol style="list-style-type: lower-latin;">([\s\S]*?)<\/ol>\s+<ol>/g, "\n<ol style=\"list-style-type: lower-latin;\">$1</ol></li>");//latin in ol
        o = o.replace(/<\/li>\s+<\/ul>\s+<ol>([\s\S]*?)<\/ol>\s+<ul>/g, "\n<ol>$1</ol></li>");//ol in ul
        o = o.replace(/<\/li>\s+<\/ul>\s+<ol style="list-style-type: lower-latin;">([\s\S]*?)<\/ol>\s+<ul>/g, "\n<ol style=\"list-style-type: lower-latin;\">$1</ol></li>");//latin in ul
        o = o.replace(/<\/li>\s+<\/ol>\s+<ol style="list-style-type: lower-latin;">([\s\S]*?)<\/ol>\s+<ol>/g, "\n<ol style=\"list-style-type: lower-latin;\">$1</ol></li>");//ul in latin
        o = o.replace(/<\/li>\s+<\/ul>\s+<ol style="list-style-type: lower-latin;">([\s\S]*?)<\/ol>\s+<ul>/g, "\n<ol style=\"list-style-type: lower-latin;\">$1</ol></li>");//ul in latin
        //remove empty tags. this is needed a second time to delete empty lists that were created to fix nesting, but weren't needed
        o = o.replace(new RegExp("\\s?<(" + remptys + ")>\s*<\\/\\1>", 'gi'), '');

        return o;
    },

    //array equals
    //loops through all the elements of an array to see if any of them equal the test.
    ae: function(needle, haystack)
    {
        if (typeof(haystack) == 'object')
            for (var i = 0; i < haystack.length; i++)
                if (needle == haystack[i])
                    return true;

        return false;
    },

    //get character
    //return specified character from d
    getc:function(text,i)
    {
        return text.charAt(i);
    },

    //end of attr
    //determines if their is anything but spaces between the current character, and the next equals sign
    endOfAttr:function(text,i)
    {
        var between = text.substring(i+1, text.indexOf('=', i+1));
        if (between.replace(/\s+/g, ''))
            return true;
        else
            return false;
    },

    htmlentities:function(character)
    {
        if (this.hents[character])
            return this.hents[character];
        else
            return character;
    }
};

