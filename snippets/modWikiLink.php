id: 52
source: 1
name: modWikiLink
category: 'Output Modifiers'
properties: 'a:0:{}'

-----

function doMarkdownLinks($s) {
    return preg_replace_callback('/\[(.*?)\]\((.*?)\)/', function ($matches) {
        return '<a href="' . $matches[2] . '">' . $matches[1] . '</a>';
    }, htmlspecialchars($s));
}

return doMarkdownLinks($input);