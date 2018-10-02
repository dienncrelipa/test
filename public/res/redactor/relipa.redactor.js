/**
 * Created by admin on 7/26/16.
 */
$.Redactor.prototype.chatbox = function () {
  return {
    init: function () {
      var chatboxbtn = this.button.addAfter('qabox', 'chatbox-btn', '対話');
      
      this.button.addCallback(chatboxbtn, this.chatbox.chatbox);
    },
    chatbox: function (currentElement) {
      var _this = this;
      var conv_name = '';
      var conv_img = '';
      var conv_text = '';
      var is_url = false;
      var is_right = $(currentElement).find('div.convtext2').length ? 'checked' : '';
      if(typeof  currentElement !== undefined && currentElement.tagName !== undefined){
          conv_name = $(currentElement).find('div.convname:first').text();
          conv_img = $(currentElement).find('img:first').attr('src');
          conv_text = $(currentElement).find('div.convtext:first').text();
          conv_text += $(currentElement).find('div.convtext2:first').text();
          is_url = conv_img ? true : false;
      }
      var site_target_element = $('select[name="site_id"]');
      var list_target_site = site_target_element.html();
      var template = '<div class="redactor-chatbox-modal-body">' +
        '<div class="redactor-modal-tab redactor-group" data-title="General">' +
          // Chat box user name
        '<section id="redactor-modal-chatbox-message">' +
          '<div class="chatbox-message">' +
            '<div class="form-group">' +
              '<label>人物名前</label>' +
            '</div>' +
            '<div class="form-group">' +
              '<input type="text" id="chatbox-uname" placeholder="名前を入力" value="'+ conv_name +'" />' +
            '</div>' +
          '</div>' +
        '</section>' +
          // Chat box img section
        '<section id="redactor-modal-chatbox-img">' +
          '<label>画像</label>' +
          '<div class="form-group" ' + (!is_url ? 'style="display: none;"' : '') + '>' +
            '<img src="'+ conv_img +'" width="150"/>'+
          '</div>' +
          '<div class="form-group">' +
            '<select id="cb_target_site">'+list_target_site+'</select>'+
          '</div>' +
          '<div class="form-group" id="list_images" style="display: none; max-height: 100px; overflow-y: scroll">' +
          '<div id="list_images"><div class="cb-loading" style="text-align: center; font-size: 30px"><i class="fa fa fa-spinner fa-spin" aria-hidden="true"></i></div></div>'+
          '</div>' +
          '<input type="hidden" name="real-image-url" value="'+ conv_img +'"/>' +
        '</section>' +
          // Chat box message modal section
        '<section id="redactor-modal-chatbox-message">' +
          '<div class="chatbox-message">' +
            '<div class="form-group">' +
              '<label>メッセージ</label>' +
            '</div>' +
            '<div class="form-group">' +
              '<textarea style="height: 200px;" id="chatbox-message" placeholder="メッセージを入力">'+ conv_text +'</textarea>' +
            '</div>' +
          '</div>' +
        '</section>' +
          // Chat box position
        '<label>位置（デフォルトは左側）</label>' +
        '<section id="redactor-modal-chatbox-position">' +
          '<div class="chatbox-position">' +
          // uncheck = left, checked = right
            '<div class="chatbox-checkbox">' +
              '<label>' +
                '<input type="checkbox" name="chatboxpos" id="chatboxpos" '+ is_right +' /> 右側にする' +
              '</label>' +
            '</div>' +
          '</div>' +
        '</section>' +
        '<section>' +
          '<button id="redactor-modal-button-action">挿入</button>' +
          '<button id="redactor-modal-button-cancel">キャンセル</button>' +
        '</section>' +
        '</div>' +
        '</div>';

      this.modal.addTemplate('chatbox-template', template);
      this.modal.load('chatbox-template', '対話', 600);
      this.modal.show();

      // focus
      if (this.detect.isDesktop())
      {
        setTimeout(function()
        {
          $('#chatbox-uname').focus();
        }, 1);
      }

      var target_site_element = $('select[name="site_id"]').find('option:selected'),
          target_site_selected = target_site_element.val(),
          keyword_image_category = target_site_element.text();

      var cb_target_site_element =  $('select#cb_target_site');
          cb_target_site_element.find('option[value="'+target_site_selected+'"]').attr('selected', true);
          cb_target_site_element.on('change', function () {
            keyword_image_category = $(this).find("option:selected").text();
            _this.chatbox.getListPhoto(keyword_image_category);
          });
      if(keyword_image_category.length > 0) {
          _this.chatbox.getListPhoto(keyword_image_category);
      }

      $('#image-url').change(function () {
          $('input[name="real-image-url"]').val($(this).val());
      });


      this.modal.getActionButton().click($.proxy(function(){
          this.chatbox.convInsert(currentElement);
      }, this));
    },
    convInsert: function (currentElement) {
      var opts = {
        rightpos: false
      };

      var isRightPos = $('#chatboxpos').is(':checked');
      var conv = '';
      var conv_noimg = '';
      isRightPos ? conv = '2' : conv;
      $('input[name="real-image-url"]').val() == '' ? conv_noimg = 'chatbox_noimg' : conv_noimg;
      var html_content = '<div class="convimg' + conv + '">' +
          '<img class="' + conv_noimg + '" src="' + $('input[name="real-image-url"]').val() + '" title="' + $('#chat-box-site-name').val() + '" alt="' + $('#chat-box-site-url').val() + '">' +
          '<div class="convname clearfix">' +
            $('#chatbox-uname').val() +
          '</div>' +
        '</div>' +
        '<div class="convtext' + conv + '">' + $('#chatbox-message').val().replace(/\r?\n/g, '<br class="line-break">') + '</div>';
      var html_template = '<div class="conv">' + html_content +'</div><p class="clearfix">&nbsp;</p>';;
      isRightPos ? opts.rightpos = true : opts.rightpos;
      this.modal.close();
      if(typeof  currentElement !== undefined && currentElement.tagName !== undefined){
        $(currentElement).html(html_content);
        this.caret.after($(currentElement).last());
      }else{
          this.insert.html(html_template);
          this.caret.after($('div.conv').last());
      }
    },
    getListPhoto: function (category) {
        var _this = this;
        var photo_show_element = $('div#list_images');
        photo_show_element.show();
        console.log(category);
        $.ajax({
            url: '/webapp/myphotos/search-by-type?tag=対話アイコン&category='+category,
            processData: false, // important
            contentType: false, // important
            dataType : 'json',
            success: function(data) {
                if(data.error !== undefined) {
                    alert(data.error);
                } else {
                    var html = '<ul class="photo-thumbs">';
                    $.each(data.data, function (k, v) {
                        var source_url = v.source_url;
                        console.log(typeof v.thumbnail);
                        if(typeof v.thumbnail !== typeof undefined) {
                            source_url = v.thumbnail.url;
                        }
                        html += '<li>';
                        html += '<img class="cb_photo_thumb" src="'+source_url+'" height="100px" data-url="'+v.source_url+'">';
                        html += '</li>';
                    });
                    html += '</ul>';
                    photo_show_element.html(html);
                    //$('input[name="real-image-url"]').val(data.url);
                    _this.chatbox.photoSelected();
                }
            }
        });
    },
    photoSelected: function () {
        $('img.cb_photo_thumb').on('click', function () {
            $('ul.photo-thumbs img').removeClass('selected');
            $(this).addClass('selected');
            var url = $(this).data('url');
            $('input[name="real-image-url"]').val(url);
        });
    }
  }
};
$.Redactor.prototype.htmlBox = function()
{
    return {
        that_redactor : this,
        buttonName: 'html-box',
        init: function()
        {
            var button = this.button.add(this.htmlBox.buttonName, 'HTMLBox');
            this.button.addCallback(button, this.htmlBox.show);
        },
        show: function(currentEle)
        {
            var content = $(currentEle).html();
            if (currentEle === this.htmlBox.buttonName) {
                content = '';
                currentEle = false;
            }

            var template =
                '<div class="redactor-modal-tab redactor-group" data-title="General">' +
                '<section id="redactor-modal-checkbox-title">' +
                '<label>HTML</label>' +
                '<textarea style="height: 400px;" id="content-html-box">' + content + '</textarea>' +
                '</section>' +
                '<section><button id="redactor-modal-button-action" data-loading-text="Validating...">'+this.lang.get('insert')+'</button>' +
                '<button id="redactor-modal-button-cancel">'+this.lang.get('close')+'</button></section>' +
                '<div id="has-error-html-box" style="display: none"></div>'+
                '</div>';

            this.modal.addTemplate('html-box-template', template);
            this.modal.load('html-box-template', 'HTML Box', 1000);
            this.modal.show();
            this.modal.getActionButton().click($.proxy(function(){
                this.htmlBox.insert(currentEle);
            }, this));
        },
        insert: function (currentEle) {
            var _this = this;
            var content = $('#content-html-box').val();
            var hasScript = hasTagScript(content);

            if(hasScript) {
              showModalConfirm();
              return false;
            }

            var html_template = '<div class="insert-from-html-box">'+content+'</div>';

            var button_submit = $('#redactor-modal-button-action');
            button_submit.button('loading');
            $.ajax({
                url: '/ajax/validator-html',
                type: 'POST',
                dataType : 'json',
                data: { content: content},
                success: function(data) {
                    button_submit.button('reset');
                    if(data.error == true) {
                        $('#has-error-html-box').html('<div class="alert alert-danger" role="alert">'+data.message+'</div>').show();
                    } else {
                        _this.modal.close();
                        if (currentEle) {
                            $(currentEle).html(content);
                            that_redactor.caret.after($(currentEle).last());
                            return;
                        }
                        _this.insert.html(html_template);
                    }
                }
            });
        }
    };
};
