/**
 * Created by admin on 11/3/16.
 */
// real checkboxes
(function ($, undefined) {
  "use strict";

  var inp = document.createElement("INPUT");
  inp.type = "radio";
  inp.className = "jstree-realcheckbox";

  $.jstree.defaults.realcheckboxes = {};

  $.jstree.plugins.realcheckboxes = function (options, parent) {
    this.bind = function () {
      parent.bind.call(this);
      this._data.realcheckboxes.uto = false;
      this.element
        .on('changed.jstree uncheck_node.jstree check_node.jstree uncheck_all.jstree check_all.jstree move_node.jstree copy_node.jstree redraw.jstree open_node.jstree ready.jstree loaded.jstree disable_node.jstree enable_node.jstree', $.proxy(function () {
          // only if undetermined is in setting
          if (this._data.realcheckboxes.uto) {
            clearTimeout(this._data.realcheckboxes.uto);
          }
          this._data.realcheckboxes.uto = setTimeout($.proxy(this._realcheckboxes, this), 50);
        }, this));
    };
    this.redraw_node = function (obj, deep, callback) {
      obj = parent.redraw_node.call(this, obj, deep, callback);
      if (obj) {
        var i, j, tmp = null, chk = inp.cloneNode(true);
        for (i = 0, j = obj.childNodes.length; i < j; i++) {
          if (obj.childNodes[i] && obj.childNodes[i].className && obj.childNodes[i].className.indexOf("jstree-anchor") !== -1) {
            tmp = obj.childNodes[i];
            break;
          }
        }
        if (tmp) {
          for (i = 0, j = tmp.childNodes.length; i < j; i++) {
            if (tmp.childNodes[i] && tmp.childNodes[i].className && tmp.childNodes[i].className.indexOf("jstree-checkbox") !== -1) {
              tmp = tmp.childNodes[i];
              break;
            }
          }
        }
        if (tmp && tmp.tagName === "I") {
          tmp.style.backgroundColor = "transparent";
          tmp.style.backgroundImage = "none";
          tmp.appendChild(chk);
        }
      }
      return obj;
    };
    this._realcheckboxes = function () {
      var ts = true; //this.settings.checkbox.tie_selection;
      $('.jstree-realcheckbox').each(function () {
        this.checked = (!ts && this.parentNode.className.indexOf("jstree-checked") !== -1) || (ts && this.parentNode.parentNode.className.indexOf('jstree-clicked') !== -1);
        this.indeterminate = this.parentNode.className.indexOf("jstree-undetermined") !== -1;
        this.disabled = this.parentNode.parentNode.className.indexOf("disabled") !== -1;
      });
    };
  };
})(jQuery);