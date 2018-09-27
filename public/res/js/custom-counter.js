CONFIG_RUN_COUNTER = 1000;
var counterWP = {
    settings: {
        HTMLRegExp: /<\/?[a-z][^>]*?>/gi,
        HTMLcommentRegExp: /<!--[\s\S]*?-->/g,
        spaceRegExp: /&nbsp;|&#160;/gi,
        HTMLEntityRegExp: /&\S+?;/g,
        connectorRegExp: /--|\u2014/g,
        removeRegExp: new RegExp([
            '[',
            // Basic Latin (extract)
            '\u0021-\u0040\u005B-\u0060\u007B-\u007E',
            // Latin-1 Supplement (extract)
            '\u0080-\u00BF\u00D7\u00F7',
            // General Punctuation
            '\u2000-\u2BFF',
            // Supplemental Punctuation
            '\u2E00-\u2E7F',
            ']'
        ].join(''), 'g'),
        astralRegExp: /[\uD800-\uDBFF][\uDC00-\uDFFF]/g,
        wordsRegExp: /\S\s+/g,
        characters_excluding_spacesRegExp: /\S/g,
        characters_including_spacesRegExp: /[^\f\n\r\t\v\u00AD\u2028\u2029]/g,
        l10n: window.wordCountL10n || {}
    },

    count: function (text, type) {
        var count = 0;

        type = type || counterWP.settings.l10n.type;

        if (type !== 'characters_excluding_spaces' && type !== 'characters_including_spaces') {
            type = 'words';
        }

        if (text) {
            text = text + '\n';

            text = text.replace(counterWP.settings.HTMLRegExp, '\n');
            text = text.replace(counterWP.settings.HTMLcommentRegExp, '');

            if (counterWP.settings.shortcodesRegExp) {
                text = text.replace(counterWP.settings.shortcodesRegExp, '\n');
            }

            text = text.replace(counterWP.settings.spaceRegExp, ' ');

            if (type === 'words') {
                text = text.replace(counterWP.settings.HTMLEntityRegExp, '');
                text = text.replace(counterWP.settings.connectorRegExp, ' ');
                text = text.replace(counterWP.settings.removeRegExp, '');
            } else {
                text = text.replace(counterWP.settings.HTMLEntityRegExp, 'a');
                text = text.replace(counterWP.settings.astralRegExp, 'a');
            }

            text = text.match(counterWP.settings[type + 'RegExp']);

            if (text) {
                count = text.length;
            }
        }

        return count;
    },
    decode: function (html) {
        var txt = document.createElement("textarea");
        txt.innerHTML = html;
        return txt.value;
    },
    addCountNumberEditor: function (editorValue, eSetShowNumberEditor, typeGetCharacter) {
        editorValue = editorValue ? editorValue : '#content';
        typeGetCharacter = typeGetCharacter ? typeGetCharacter : 'characters_including_spaces';
        eSetShowNumberEditor = eSetShowNumberEditor ? eSetShowNumberEditor : $('span#word-count');

        $(window).scroll(function () {
            //var html = counterWP.decode($('' + editorValue + '').val());
            var html = $('' + editorValue + '').val(),
                counterNumber = counterWP.count(html, typeGetCharacter);
            eSetShowNumberEditor.html('' + counterNumber + '');
        });
    }
};