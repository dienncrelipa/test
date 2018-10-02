var createShortcut = {
    arrShotCut: {},
    mapShortCut: {
        'ctrl+2,meta+2': {
            name: 'h2',
            func: 'blockFormat.h2'
        },
        'ctrl+3,meta+3': {
            name: 'h3',
            func: 'blockFormat.h3'
        },
        'ctrl+4,meta+4': {
            name: 'h4',
            func: 'blockFormat.h4'
        },
        'ctrl+d,meta+d': {
            name: 'deleted',
            func: 'inline.format'
        },
        'ctrl+y,meta+y': {
            name: 'yellow-marker',
            func: 'blockFormat.yellow'
        },
        'ctrl+r,meta+r': {
            name: 'red-marker',
            func: 'blockFormat.red'
        },
        'ctrl+q,meta+j': {
            name: 'quote',
            func: 'blockFormat.quote'
        },
        //'ctrl+k': {
        //    name: 'link',
        //    func: 'link.show'
        //},
        'ctrl+l,meta+l': {
            name: 'lists',
            func: 'list.toggle'
        },
        'ctrl+h,meta+h': {
            name: 'checkbox',
            func: 'blockFormat.checkbox'
        },
        'ctrl+shift+i,meta+shift+i': {
            name: 'image',
            func: 'image.show'
        }
    },
    setArrShotCut: function (shortCut, formatTye, relParams) {
        formatTye = formatTye ? formatTye : 'inline.format';
        this.arrShotCut[shortCut] = {func: '' + formatTye + '', params: ['' + relParams + '']};

        return this;
    },
    getArrShotCut: function () {
        return this.arrShotCut;
    },
    createListShortCutForRedactor: function () {
        if (!this.mapShortCut || this.mapShortCut == {}) {
            console.log('Please set shortcut key');
            return false;
        }

        for (var i in this.mapShortCut) {
            this.setArrShotCut(i, this.mapShortCut[i].func, this.mapShortCut[i].name);
        }

        return this.getArrShotCut();
    },
    createTitleForShortCuts: function (keyName, title) {
        var titleCustom = title;
        for (var i in this.mapShortCut) {
            if (keyName != this.mapShortCut[i].name) {
                continue;
            }
            i = i.split('+');
            var lastTitle = '';
            for (var j = 0; j < i.length; j++) {
                lastTitle += this.capitalizeFirstLetter(i[j]) + '+'
            }
            lastTitle = lastTitle.slice(0, -1);
            titleCustom += '(' + lastTitle + ')';
        }

        return titleCustom;
    },
    capitalizeFirstLetter: function (str) {
        str = str.toLowerCase().split(' ');
        if (!str.length) {
            return str;
        }
        for (var i = 0; i < str.length; i++) {
            str[i] = str[i].split('');
            str[i][0] = str[i][0].toUpperCase();
            str[i] = str[i].join('');
        }

        return str.join(' ');
    }
};
