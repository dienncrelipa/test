var that_redactor = '';
var eml_box = '';
function modalReviewBox(obj_redactor, elm){
    eml_box = elm;
    var ranktitle = $(elm).find('div.ranktitle').text();
    var ranktext = $(elm).find('div.ranktext').text();
    var ranksource = $(elm).find('a.ranksource.profile').text();
    var ranksource_site = $(elm).find('a.ranksource.site').first().text();
    var ranksource_url = $(elm).find('a.ranksource.profile').attr('href');
    var ranksource_url_site = $(elm).find('a.ranksource.site').attr('href');
    ranksource_url = typeof ranksource_url !== 'undefined' ? ranksource_url : '';
    ranksource_url_site = typeof ranksource_url_site !== 'undefined' ? ranksource_url_site : '';
    var rankprofile = $(elm).find('div.rankprofile').text();
    var rankimg = $(elm).find('div.rankimg').find('img').attr('src');
    rankimg = typeof rankimg !== 'undefined' ? rankimg : ''; 
    var rankstart = $(elm).find('div.rankstar-border').text();
    rankstart = rankstart.split('\/');
    rankstart =  typeof rankstart[0] !== 'undefined' ? rankstart[0].trim() : '';
        var template =
                '<div class="redactor-modal-tab redactor-group" data-title="General">' +
                '<section id="redactor-modal-review-box-title">' +
                '<label>タイトル</label>' +
                '<input type="text" id="review-box-title" value="'+ ranktitle +'"/>' +
                '</section>' +
                '<section id="redactor-modal-review-box-content">' +
                '<label>本文</label>' +
                '<textarea style="height: 200px;" id="review-box-content">'+ ranktext +'</textarea>' +
                '</section>' ;
        if(rankimg !== ''){
            template +=
                    '<section>' +
                    '<img src="'+rankimg+'" with="50" height="50">' +
                    '</section>' ;
        }
        template +=
                '<section>' +
                '<label>画像（任意）</label>' +
                '<input type="radio" name="image-insert-type" id="image-insert-type-upload" value="upload" checked onclick="$(\'#image-upload\').show();$(\'#image-url, #image-url-info\').hide();"> アップロード &nbsp; ' +
                '<input type="radio" name="image-insert-type" id="image-insert-type-url" value="url" onclick="$(\'#image-upload\').hide();$(\'#image-url, #image-url-info\').show();"> URL &nbsp; ' +
                '<br />' +
                '<div>' +
                '<input type="file" name="image-upload" id="image-upload" data-width="200" data-height="" />' +
                '<input type="text" name="image-url" id="image-url" placeholder="画像URL ※直リンクの場合必須"  style="display:none;" value="'+rankimg+'"/>' +
                '<input type="hidden" name="real-image-url" />' +
                '</div>' +
                '<div id="image-url-info" style="display: none;">' +
                '<input type="text" id="review-box-site-url" placeholder="画像参照元URL ※直リンクの場合必須" value="'+ranksource_url_site+'"/>' +
                '<br />' +
                '<input type="text" id="review-box-site-name" placeholder="参照元サイト名" value="'+ranksource_site+'"/>' +
                '</div>' +
                '</section>' +
                '<section id="redactor-modal-review-box-content">' +
                '<label>プロフィール（任意）</label>' +
                '<input type="text" id="review-box-profile" value="'+rankprofile+'"placeholder="口コミした人の年齢や性別などを入力" />' +
                '</section>' +
                '<section id="redactor-modal-review-box-content">' +
                '<label>口コミ参照元<br><span style="font-size: 9px; color: rgba(2, 6, 6, 0.77);">※編集部で集めた口コミ引用の場合は、参照URLは「空欄」、参照サイト名は「編集部独自調べ」と記載してください</span></label>' +
                '<input type="text" id="review-box-ref-site-url-2" value="'+ranksource_url+'" placeholder="口コミの参照元サイトのURLを入力" />' +
                '<input type="text" id="review-box-ref-site-name"  value="'+ranksource+'" placeholder="口コミの参照元サイトのサイト名を入力" />' +
                '</section>' +
                '<section id="redactor-modal-review-box-content">' +
                '<label>評価（任意。星が5個中何個分かを半額数字で入力。小数点以下2桁まで）</label>' +
                '<input type="number" id="review-box-rate" placeholder="5.00" min="1" max="5" step=".1" value="'+rankstart+'"/>' +
                '</section>' +
                '<button id="redactor-modal-button-action">挿入</button>' +
                '<button id="redactor-modal-button-cancel">キャンセル</button>' +
                '</section>' +
                '</div>';

        obj_redactor.modal.addTemplate('review-box-template', template);
        obj_redactor.modal.load('review-box-template', '口コミ', 600);
        obj_redactor.modal.show();

        setTimeout(function() {
                $('#review-box-title').focus();
        }, 1);

        $('#review-box-rate').on('keyup input paste', function () {
                if(isNaN(Number($(this).val())) || Number($(this).val()) > Number($(this).attr('max')) || !$.isNumeric($(this).val())) {
                        $(this).val('');
                }
        });

        $('#image-upload').change(function(event){
                $('button#redactor-modal-button-action').attr('disabled', 'disabled');

                var file = event.target.files[0];
                var formData = new FormData();

                formData.append('file', file);
                if ($(this).data('width')) formData.append('width', $(this).data('width'));
                if ($(this).data('height')) formData.append('height', $(this).data('height'));

                $.ajax({
                        url: '/ajax/image',
                        type: 'POST',
                        processData: false, // important
                        contentType: false, // important
                        dataType : 'json',
                        data: formData,
                        success: function(data) {
                                if(data.error !== undefined) {
                                        alert(data.error);
                                        $('#image-upload').val('');
                                } else {
                                        $('input[name="real-image-url"]').val(data.url);
                                }
                                $('button#redactor-modal-button-action').removeAttr('disabled');
                        }
                });
        });
        obj_redactor.modal.getActionButton().click(obj_redactor.blockFormat.reviewBoxInsert);
}
function insertReviewBox(obj_redactor){
    var imageUrl = $('#image-insert-type-upload').is(':checked') ? $('input[name="real-image-url"]').val() : $('input[name="image-url"]').val();
    var sourceImage = ($('#image-insert-type-upload').is(':checked')) ? '' : '<p class="ranksource"><a class ="ranksource site" href="'+$('#review-box-site-url').val()+'" rel="nofollow" target="_blank">'+$('#review-box-site-name').val()+'</a></p>';
    if(imageUrl == '' && eml_box){
        var rankimg = $(eml_box).find('div.rankimg').find('img').attr('src');
        if(typeof rankimg !== 'undefined' ){
            imageUrl = rankimg;
        }
        sourceImage = '<p class="ranksource"><a class ="ranksource site" href="'+$('#review-box-site-url').val()+'" rel="nofollow" target="_blank">'+$('#review-box-site-name').val()+'</a></p>';
    }

    var templateInsert = '';
    var img_src_mobile = $('#image-url').val() == '' ? '' : '<span class="dsp-src-mobile"><a class ="ranksource" href="' + $('#image-url').val() + '" rel="nofollow" target="_blank">' + $('#review-box-site-name').val() + '</a>&nbsp;&nbsp;&nbsp;&nbsp;</span>';
	  var rankstar_val = Number(parseFloat($('#review-box-rate').val()).toFixed(1));
		var rankstar_valToStr = rankstar_val.toString();
    var rankstar = ( rankstar_val > 0) ? '<div class="rankstar-border"><div class="rankstar rankstar' + rankstar_valToStr.replace('.', '-') + '"></div> <div class="rankstar-mobile rankstar' + rankstar_valToStr.replace('.', '-') + '"></div>' + rankstar_val + '/5</div>' : '';

    if(imageUrl != '') {
      if(!eml_box)
      templateInsert = '<div class="ranking reviewbox" contenteditable="false" >';
      templateInsert += '<ul class="list-inline">' +
        '<li><div class="rankimg clearfix">' +
        '<img style="width: 100%" src="' + imageUrl + '"  alt="' + $('#review-box-title').val() + '"/>' + sourceImage +
        '</div></li>' +
        '<li><div class="ranktitle">' + $('#review-box-title').val() + '</div>' +
                                            '<div class="rankimg-mobile clearfix">' +
                                            '<img style="width: 100%" src="' + imageUrl + '"  alt="' + $('#review-box-title').val() + '"/>' + sourceImage +
                                            '</div>' +
        rankstar +
        '<div class="rankprofile">' + $('#review-box-profile').val() + '</div>' +
        '<div class="ranktext">' + $('#review-box-content').val() + '</div>' +
        '<p class="ranksource">' + img_src_mobile + '<a class ="ranksource profile" href="' + $('#review-box-ref-site-url-2').val() + '" rel="nofollow" target="_blank">' + $('#review-box-ref-site-name').val() + '</a></p></li>';
        templateInsert +='</div>';
      if(!eml_box)
        templateInsert +='<p class="clearfix"><br/ ></p> ';
    } else {
        if(!eml_box)
        templateInsert = '<div class="ranking no-img reviewbox" contenteditable="false" >';
        templateInsert += '<ul class="list-inline">' +
          '<li class="rank-img">	&nbsp;</li>' +
          '<li class="rank-content"><div class="ranktitle">' + $('#review-box-title').val() + '</div>' +
          rankstar +
          '<div class="rankprofile">' + $('#review-box-profile').val() + '</div>' +
          '<div class="ranktext">' + $('#review-box-content').val() + '</div>' +
          '<p class="ranksource"><a class ="ranksource profile" href="' + $('#review-box-ref-site-url-2').val() + '" rel="nofollow" target="_blank">' + $('#review-box-ref-site-name').val() + '</a></p>'+
          '</li></ul>';
        templateInsert +='</div>';
        templateInsert +='<p class="clearfix"><br/ ></p> ';
    }

    obj_redactor.modal.close();
    if(eml_box){
        $(eml_box).html(templateInsert);
        obj_redactor.caret.after($(eml_box).last());
    }else{
         obj_redactor.insert.html(templateInsert);
         obj_redactor.caret.after($('div.ranking').last());
    }
}
function modalCheckbox(obj_redactor, elm){
    eml_box = elm;
    var checktitle = $(elm).find('h3.checktitle').first().text();
    var checktext = '';
    var checktext_list = $(elm).find('ul.checktext').first();
    if(typeof checktext_list !== 'undefined'){
        $(checktext_list).find('li').each(function (index, li_elm){
            if($(li_elm).text() != ''){
                checktext += $(li_elm).text() + '\n';
            }
        });
    }
    var template =
                '<div class="redactor-modal-tab redactor-group" data-title="General">' +
                '<section id="redactor-modal-checkbox-title">' +
                '<label>タイトル</label>' +
                '<input type="text" id="checkbox-title" value="'+checktitle+'"/>' +
                '</section>' +
                '<section id="redactor-modal-checkbox-content">' +
                '<label>コンテンツ</label>' +
                '<textarea style="height: 200px;" id="checkbox-content" >'+checktext+'</textarea>' +
                '</section>' +
                '<button id="redactor-modal-button-action">挿入</button>' +
                '<button id="redactor-modal-button-cancel">キャンセル</button>' +
                '</section>' +
                '</div>';

        obj_redactor.modal.addTemplate('checkbox-template', template);
        obj_redactor.modal.load('checkbox-template', 'チェック', 600);
        obj_redactor.modal.show();

        obj_redactor.modal.getActionButton().click(obj_redactor.blockFormat.checkboxInsert);
}
function insertCheckbox(obj_redactor){
    var title = $('#checkbox-title').val();
    var content = $('#checkbox-content').val();
    var templateInsert = '';
    if(!eml_box){
        templateInsert += '<div class="checkbox" contenteditable="false">';
    }
        templateInsert +='<h3 class="checktitle">'+title+'</h3>' +
            '<ul class="checktext">' +
            obj_redactor.blockFormat.checkboxProcess(content) +
            '</ul>';
    if(!eml_box){
        templateInsert +='</div>' +
            '<p class="clearfix">&nbsp;</p>';
    }

    obj_redactor.modal.close();
    if(eml_box){
        $(eml_box).html(templateInsert);
        obj_redactor.caret.after($(eml_box).last());
    }else{
        obj_redactor.insert.html(templateInsert);
        obj_redactor.caret.after($('div.checkbox').last());
    }
}
(function($)
{
	$.Redactor.prototype.source = function()
	{
		return {
			init: function()
			{
				var button = this.button.add('html', 'HTML');
				this.button.addCallback(button, this.source.toggle);

				var style = {
					'width': '100%',
					'margin': '0',
					'background': '#111',
					'box-sizing': 'border-box',
					'color': 'rgba(255, 255, 255, .8)',
					'font-size': '14px',
					'outline': 'none',
					'padding': '16px',
					'line-height': '22px',
					'font-family': 'Menlo, Monaco, Consolas, "Courier New", monospace'
				};

				this.source.$textarea = $('<textarea />');
				this.source.$textarea.css(style).hide();

				if (this.opts.type === 'textarea')
				{
					this.core.box().append(this.source.$textarea);
				}
				else
				{
					this.core.box().after(this.source.$textarea);
				}

				this.core.element().on('destroy.callback.redactor', $.proxy(function()
				{
					this.source.$textarea.remove();

				}, this));

			},
			toggle: function()
			{
				return (this.source.$textarea.hasClass('open')) ? this.source.hide() : this.source.show();
			},
			setCaretOnShow: function()
			{
				this.source.offset = this.offset.get();
				var scroll = $(window).scrollTop();

				var	width = this.core.editor().innerWidth();
				var height = this.core.editor().innerHeight();

				// caret position sync
				this.source.start = 0;
				this.source.end = 0;
				var $editorDiv = $("<div/>").append($.parseHTML(this.core.editor().html(), document, true));
				var $selectionMarkers = $editorDiv.find("span.redactor-selection-marker");

				if ($selectionMarkers.length > 0)
				{
					var editorHtml = $editorDiv.html().replace(/&amp;/g, '&');
					//var editorHtml = $editorDiv.html();

					if ($selectionMarkers.length === 1)
					{
						this.source.start = this.utils.strpos(editorHtml, $editorDiv.find("#selection-marker-1").prop("outerHTML"));
						this.source.end = this.source.start;
					}
					else if ($selectionMarkers.length === 2)
					{
						this.source.start = this.utils.strpos(editorHtml, $editorDiv.find("#selection-marker-1").prop("outerHTML"));
						this.source.end = this.utils.strpos(editorHtml, $editorDiv.find("#selection-marker-2").prop("outerHTML")) - $editorDiv.find("#selection-marker-1").prop("outerHTML").toString().length;
					}
				}

			},
			setCaretOnHide: function(html)
			{
				this.source.start = this.source.$textarea.get(0).selectionStart;
				this.source.end = this.source.$textarea.get(0).selectionEnd;

				// if selection starts from end
				if (this.source.start > this.source.end && this.source.end > 0)
				{
					var tempStart = this.source.end;
					var tempEnd = this.source.start;

					this.source.start = tempStart;
					this.source.end = tempEnd;
				}

				this.source.start = this.source.enlargeOffset(html, this.source.start);
				this.source.end = this.source.enlargeOffset(html, this.source.end);

				html = html.substr(0, this.source.start) + this.marker.html(1) + html.substr(this.source.start);

				if (this.source.end > this.source.start)
				{
					var markerLength = this.marker.html(1).toString().length;

					html = html.substr(0, this.source.end + markerLength) + this.marker.html(2) + html.substr(this.source.end + markerLength);
				}


				return html;

			},
			hide: function()
			{
				this.source.$textarea.removeClass('open').hide();
				this.source.$textarea.off('.redactor-source');

				var code = this.source.$textarea.val();

				code = this.paragraphize.load(code);
				//code = this.source.setCaretOnHide(code);

				this.code.start(code);
				this.button.enableAll();
				this.core.editor().show().focus();
				this.selection.restore();
				//this.code.sync();
			},
			show: function()
			{
				this.selection.save();
				this.source.setCaretOnShow();

				var height = this.core.editor().innerHeight();
				var code = this.code.get();

				code = code.replace(/\n\n\n/g, "\n");
				code = code.replace(/\n\n/g, "\n");

				this.core.editor().hide();
				this.button.disableAll('html');
				this.source.$textarea.val(code).height(height).addClass('open').show();
				this.source.$textarea.on('keyup.redactor-source', $.proxy(function()
				{
					if (this.opts.type === 'textarea')
					{
						this.core.textarea().val(this.source.$textarea.val());
					}

				}, this));

				this.marker.remove();

				$(window).scrollTop(scroll);

				if (this.source.$textarea[0].setSelectionRange)
				{
					this.source.$textarea[0].setSelectionRange(this.source.start, this.source.end);
				}

				this.source.$textarea[0].scrollTop = 0;

				setTimeout($.proxy(function()
				{
					this.source.$textarea.focus();

				}, this), 0);
			},
			enlargeOffset: function(html, offset)
			{
				var htmlLength = html.length;
				var c = 0;

				if (html[offset] === '>')
				{
					c++;
				}
				else
				{
					for(var i = offset; i <= htmlLength; i++)
					{
						c++;

						if (html[i] === '>')
						{
							break;
						}
						else if (html[i] === '<' || i === htmlLength)
						{
							c = 0;
							break;
						}
					}
				}

				return offset + c;
			}
		};
	};
})(jQuery);
(function($)
{
	$.Redactor.prototype.counter = function()
	{
		return {
			init: function()
			{
				window.instanceRedactor = this;
				if (typeof this.opts.callbacks.counter === 'undefined')
				{
					return;
				}



				this.core.editor().on('keyup.redactor-plugin-counter', $.proxy(this.counter.count, this));
			},
			count: function()
			{
				// Custom 8.11.2016
				var cloneEditor = $('<div></div>').append(this.code.get());
				var exceptElements = cloneEditor.find('script, style, blockquote, p.quote-origin-redactor, iframe, div.ranking, div.url-debugger, div.head_title_block, div.insta_info, div.insta_info');
                                var counterNumber = 0;
				exceptElements.each(function(i, e) {
                                        if($(e).prop("tagName").toLowerCase() !== 'iframe'){
                                            $(e).find('style').remove();
                                            counterNumber += counterWP.count(e.innerHTML, 'characters_including_spaces');
                                        }
					$(e).remove();
				});
				var html = cloneEditor[0].innerHTML;
                                var counterNumberOfEditor = counterWP.count(html, 'characters_including_spaces');
                                counterNumber += counterNumberOfEditor;
				html = counterWP.decode(html);
				// END - Custom 8.11.2016
                $('span.review-box-control').remove();
				this.core.callback('counter', { counterNumber : counterNumber, counterNumberOfEditor: counterNumberOfEditor });

			}
		};
	};

	$.Redactor.prototype.blockFormat = function()
	{
		return {
			init: function ()
			{
				// Init insta
				window.embededScriptInsta = false;
                                that_redactor = this;

                var h2 = this.button.addFirst('h2', 'H2');
                var h3 = this.button.addAfter('h2', 'h3', 'H3');
                var h4 = this.button.addAfter('h3', 'h4', 'H4');
                var ranking = this.button.addBefore('bold', 'ranking', 'ランキング');
                var u = this.button.addAfter('bold', 'u', 'U');
                var yellow = this.button.addAfter('u', 'yellow-marker', 'マーカー');
                var red = this.button.addAfter('yellow-marker', 'red-marker', '赤文字');
                var checkbox = this.button.addAfter('lists', 'checkbox', 'CheckBox');
                var qabox = this.button.addAfter('checkbox', 'qabox', 'QA');
                var quote = this.button.addAfter('red-marker', 'quote', '引用');
                var reviewBox = this.button.addAfter('quote', 'review-box', '口コミ');
                var instagram = this.button.addAfter('image', 'instagram', 'Instagram');
                var fetchUrl = this.button.addBefore('html', 'fetch_url', 'URL内容取得');
                var largerText = this.button.addBefore('yellow-marker','larger_text', '大');
                var tableOfContents = this.button.addBefore('html', 'table_of_contents', '目次');
                var html = this.button.get('html');
                var center = this.button.addAfter('table_of_contents', 'center', 'Center');

				this.button.addCallback(h2, this.blockFormat.h2);
				this.button.addCallback(h3, this.blockFormat.h3);
				this.button.addCallback(h4, this.blockFormat.h4);
                this.button.addCallback(center, this.blockFormat.center);
				this.button.addCallback(u, this.blockFormat.u);
				this.button.addCallback(red, this.blockFormat.red);
				this.button.addCallback(yellow, this.blockFormat.yellow);
				this.button.addCallback(quote, this.blockFormat.quote);
				this.button.addCallback(reviewBox, this.blockFormat.reviewBox);
				this.button.addCallback(checkbox, this.blockFormat.checkbox);
				this.button.addCallback(ranking, this.blockFormat.rankingTitle);
				this.button.addCallback(qabox, this.blockFormat.qaBox);
				this.button.addCallback(instagram, this.blockFormat.instagram);
				this.button.addCallback(fetchUrl, this.blockFormat.fetchUrl);
				this.button.addCallback(largerText, this.blockFormat.largerText);
				this.button.addCallback(tableOfContents, this.blockFormat.tableOfContents);
				this.button.addCallback(html, this.blockFormat.html);
			},
			h2: function()
			{
				this.blockFormat.specialFormat('h2');
			},
			h3: function()
			{
                this.blockFormat.specialFormat('h3');
			},
			h4: function()
			{
				this.blockFormat.specialFormat('h4');
			},
            center: function()
            {
                var blocks = $(this.selection.blocks());
                var willCenter = false;
                $.each(blocks, function(k, block){
                    if($(block).hasClass('redactor-editor')) {
                        willCenter = false;
                        return false;
                    }
                    if($(block).css('text-align') != 'center') {
                        willCenter = true;
                    }
                });

                if(willCenter) {
                    blocks.addClass('text-center');
                    blocks.css({'text-align': "center"});
                } else {
                    blocks.removeClass('text-center');
                    blocks.css({'text-align': ""});
                }
            },
            specialFormat: function(tag)
            {
                if(this.selection.block().tagName != tag.toUpperCase()) {
                    document.execCommand('formatBlock', false, tag);
                } else {
                    this.block.format(tag)
                }
            },
			u: function()
			{
				this.inline.format('u');
			},
			red: function()
			{
				this.inline.format('red');
			},
			yellow: function()
			{
				this.inline.format('yellow');
			},
			quote: function()
			{
				var template =
					'<div class="redactor-modal-tab redactor-group" data-title="General">' +
					'<section id="redactor-modal-quote-content">' +
					'<label>引用コンテツ</label>' +
					'<textarea style="height: 200px;" id="quote-content" data-type="empty"></textarea>' +
					'</section>' +
					'<section id="redactor-modal-quote-origin">' +
					'<label>引用ソースURL</label>' +
					'<input type="text" id="quote-origin" />' +
					'</section>' +
					'<section>' +
					'<label>引用リンクの表示テキスト</label>' +
					'<input type="text" id="quote-origin-text" />' +
					'</section>' +
					'<section>' +
					'<button id="redactor-modal-button-action">挿入</button>' +
					'<button id="redactor-modal-button-cancel">キャンセル</button>' +
					'</section>' +
					'</div>';
				this.modal.addTemplate('quote-template', template);

				if(this.selection.text().length == 0) {
					this.modal.load('quote-template', '引用', 600);
					this.modal.show();

					this.modal.getActionButton().click(this.blockFormat.quoteInsert);
				} else {
					if(!this.utils.isTag(this.selection.parent(), 'blockquote')) {
						//this.selection.replace('<blockquote>'+this.selection.html()+'</blockquote>');
						this.modal.load('quote-template', 'Quote', 600);
						this.modal.show();
						$('#quote-content').val(this.selection.html()).data('type', 'select');
						$('#redactor-modal-quote-content').hide();
						this.modal.getActionButton().click(this.blockFormat.quoteInsert);
					} else {
						var id = $(this.selection.parent()).data('quote');
						$('p[data-quote-origin="'+id+'"]').remove();
						this.inline.format('blockquote');
					}
				}

			},
			quoteInsert: function()
			{
				var random = Math.round(Math.random()*1000000000000);
				var nodeContent = $('#quote-content').val();
				nodeContent = nodeContent.replace(/\r/gi, '');
				nodeContent = nodeContent.replace(/\n/gi, '<br/ >');

				var nodeQuote = $('<blockquote />').html(nodeContent).attr('data-quote', random);
				var nodeSource = $('');
				var origin = $('#quote-origin').val();
				if(origin.length > 0) {
					var originText = $('#quote-origin-text').val();
					originText = (originText.length > 0) ? originText : origin;
					nodeSource = $('<p class="quote-origin-redactor" style="font-size: 11px; "/>')
						.html('Source: <a href="'+origin+'">'+originText+'</a>')
						.attr('data-quote-origin', nodeQuote.data('quote'));
				}
				this.modal.close();

				if($('#quote-content').data('type') == 'select') {
					this.selection.replace(
						$("<div />").append(nodeQuote.clone()).html() +
						$("<div />").append(nodeSource.clone()).html()
					);
				} else {
					this.insert.node(nodeQuote);
					this.insert.node(nodeSource);
				}
			},
			checkbox: function()
			{
                            that_redactor = this;
                            modalCheckbox(this, '');
			},
			checkboxInsert: function()
			{
                                that_redactor = this;
				insertCheckbox(this);
			},
			checkboxProcess: function(content)
			{
				content = content.replace(/\r\n/g, '\n');
				var returnContent = '';
				var arrLine = content.split('\n');
				$.each(arrLine, function(index, value) {
					if(value.length == 0) {
						return;
					}

					returnContent += '<li>'+value+'</li>';
				});
				return returnContent;
			},
			reviewBox: function()
			{
                            that_redactor = this;
                            modalReviewBox(this, '');
			},
			reviewBoxInsert: function()
			{
                            that_redactor = this;
                            insertReviewBox(this);
			},
			rankingTitle: function(currentElement)
			{
				var rank_number = '';
				var rank_title = '';
				var rank_url = '';
                if(typeof  currentElement !== undefined && currentElement.tagName !== undefined) {
                    rank_number = parseInt($(currentElement).attr('class').replace('rank rank',''));
                    rank_number = rank_number !== NaN ? rank_number : '';
                    rank_title = $(currentElement).find('a:first').text();
                    rank_url = $(currentElement).find('a:first').attr('href');
                    rank_url = rank_url != 'javascript:void(0)' ? rank_url : '';
                }
				var template =
					'<div class="redactor-modal-tab redactor-group" data-title="General">' +
					'<section id="redactor-modal-checkbox-title">' +
					'<label>ランキング</label>' +
					'<input type="number" min="1" max="20" id="ranking-number" value="'+ rank_number +'" />' +
					'</section>' +
					'<section id="redactor-modal-checkbox-content">' +
					'<label>ランキング表示名</label>' +
					'<input type="text" class="form-control" id="ranking-title" required value="'+ rank_title +'"/>' +
					'</section>' +
					'<section id="redactor-modal-checkbox-content">' +
					'<label>URL</label>' +
					'<input type="text" class="form-control" id="ranking-url" value="'+ rank_url +'"/>' +
					'</section>' +
					'<section>' +
					'</section>' +
					'<section>' +
					'<button id="redactor-modal-button-action">挿入</button>' +
					'<button id="redactor-modal-button-cancel">キャンセル</button>' +
					'</section>' +
					'</div>';

				this.modal.addTemplate('ranking-template', template);
				this.modal.load('ranking-template', 'ランキング見出し', 600);
				this.modal.show();
				// focus
				if (this.detect.isDesktop())
				{
					setTimeout(function()
					{
						$('#ranking-number').focus();
					}, 1);
				}

				$('#pick-prod').click(function(){
					var left  = ($(window).width()/2)-(900/2),
						top   = ($(window).height()/2)-(600/2),
						popup = window.open ("/webapp/product/popup/picker", "popup", "width=1000, height=600, top="+top+", left="+left);
				});

				rankingPickUrlProd = function(url) {
					$('#ranking-url').val(url);
				};

				$('#ranking-number').on('keyup input paste', function () {
					if(isNaN(Number($(this).val())) || Number($(this).val()) > Number($(this).attr('max')) || !$.isNumeric($(this).val())) {
						$(this).val('');
					}
				});

                this.modal.getActionButton().click($.proxy(function(){
                    this.blockFormat.rankingTitleInsert(currentElement);
                }, this));
			},
			rankingTitleInsert: function(currentElement)
			{
				var url = $('#ranking-url').val().length > 0 ? $('#ranking-url').val() : 'javascript:void(0)';
				if($('#ranking-title').val().length == 0) {
					alert('ランキング表示名が必要です。');
					return;
				}
				var zero_prefix = $('#ranking-number').val();
				$('#ranking-number').val() <= 9 ? zero_prefix = '0' + zero_prefix : zero_prefix;
				var class_h3 = 'rank rank' + zero_prefix;
				var contentInsert ='<a class="no'+zero_prefix+'" href="'+url+'" rel="nofollow" target="_blank">'+$('#ranking-title').val()+'</a>';
				this.modal.close();
                if(typeof  currentElement !== undefined && currentElement.tagName !== undefined) {
                	$(currentElement).html(contentInsert).attr('class', class_h3);
                    this.caret.after($(currentElement).last());
                }else{
                    var templateInsert = '<h3 class="' + class_h3 + '">'+ contentInsert + '</h3><p class="clearfix">&nbsp;</p>';
                    this.insert.html(templateInsert);
                    this.caret.after($('h3.rank').last());
				}
			},
			qaBox: function(currentElement)
			{
                var question = '';
                var answer = '';
                if(typeof  currentElement !== undefined && currentElement.tagName !== undefined) {
                    question = $(currentElement).find('h3.question').text();
                    answer = $(currentElement).find('div.answer').text();
                }
				var template =
					'<div class="redactor-modal-tab redactor-group" data-title="General">' +
					'<section id="redactor-modal-checkbox-title">' +
					'<label>質問</label>' +
					'<textarea style="height: 200px;" id="qabox-question" >'+question+'</textarea>' +
					'</section>' +
					'<section id="redactor-modal-checkbox-content">' +
					'<label>回答</label>' +
					'<textarea style="height: 200px;" id="qabox-answer" >'+answer+'</textarea>' +
					'</section>' +
					'<button id="redactor-modal-button-action">挿入</button>' +
					'<button id="redactor-modal-button-cancel">キャンセル</button>' +
					'</section>' +
					'</div>';

				this.modal.addTemplate('qabox-template', template);
				this.modal.load('qabox-template', 'QA', 600);
				this.modal.show();

                this.modal.getActionButton().click($.proxy(function(){
                    this.blockFormat.qaBoxInsert(currentElement);
                }, this));
			},
			qaBoxInsert: function(currentElement)
			{
                var insideHtml = '<h3 class="question">'+$('#qabox-question').val().replace(/\r?\n/g, '<br class="line-break">')+'</h3>' +
                    '<div class="answer"><p>' +
                    $('#qabox-answer').val().replace(/\r?\n/g, '<br class="line-break">') +
                    '</p></div>';
				var templateInsert =
					'<div class="qa" contenteditable="false">' + insideHtml + '</div>';

				this.modal.close();

                if(typeof  currentElement !== undefined && currentElement.tagName !== undefined) {
                    $(currentElement).html(insideHtml);
                } else {
                    this.insert.html(templateInsert);
                    this.caret.after($('div.qa').last());
                }
			},
			instagram: function()
			{
				var template =
					'<div class="redactor-modal-tab redactor-group" data-title="General">' +
					'<section id="redactor-modal-checkbox-title" style="cursor: pointer;">' +
					'<label>Instagram URL</label>' +
					'<input type="text" id="insta-url" class="form-control" />' +
					'<input type="checkbox" id="slim-design" checked style="cursor: pointer;" />スリムデザイン' +
					'</section>' +
					'<section>' +
					'<button id="redactor-modal-button-action">挿入</button>' +
					'<button id="redactor-modal-button-cancel">キャンセル</button>' +
					'</section>' +
					'</div>';

				this.modal.addTemplate('insta-template', template);
				this.modal.load('insta-template', 'Instagram', 600);
				this.modal.show();

				this.modal.getActionButton().click(this.blockFormat.instagramInsert);
			},
			instagramInsert: function()
			{
				var instaUrl = $('#insta-url').val();
				var isSlim = $('#slim-design').is(':checked');
                // var isSlim = true;

				var that = this;

				$.ajax({
					url: 'https://api.instagram.com/oembed/?url='+instaUrl,
					type: 'GET',
					crossDomain: true,
					dataType: 'jsonp',
					success: $.proxy(function(data){
						if(typeof data !== 'object') {
							alert('無効なリンクです');
							return;
						}

						if(isSlim) {
							data.instaUrl = instaUrl;
							this.blockFormat.instagramSlim(data);
							return;
						}


						var node = $(data.html);
						$.each(node, function(k, e){
							if(e.outerHTML === undefined) {
								return;
							}
							if(e.tagName == 'SCRIPT' && window.embededScriptInsta == false) {
								$("head").append(e);
								window.embededScriptInsta = true;
							}
							that.insert.raw(e.outerHTML);

							if(window.embededScriptInsta && window.instgrm !== undefined) {
								window.instgrm.Embeds.process();
							}
						});
					}, this),
					error: function() {
						alert('無効なリンクです');
					}
				});


				this.modal.close();
			},
			instagramSlim: function(data)
			{
                var tmpUrl		= new URL(data.instaUrl),
                    c 			= (tmpUrl.pathname.slice(-1) === '/') ? '' : '/',
                    thumbnail	= tmpUrl.protocol + '//' + tmpUrl.hostname + tmpUrl.pathname + c + 'media/?size=l';

				var templateInsert =
					'<div class="insta-slim" style="max-width: '+data.thumbnail_width+'px">' +
					'<div class="insta-image">' +
					'<img src="'+ thumbnail +'">'+
					'</div>'+
					'<div class="insta_info">'+
					'<a href="'+data.instaUrl+'" target="_blank" class="insta_user">'+
					data.author_name+
					'</a>'+
					'<div class="icon-mery icon-credit-instagram"></div>'+
					'</div>'+
					'</div>';

				this.insert.html(templateInsert);
			},
			fetchUrl: function(currentElement)
			{
				var url_fetch = '';
                if(typeof  currentElement !== undefined && currentElement.tagName !== undefined) {
                    url_fetch = $(currentElement).find('div.url-thumbnail').find('a').attr('href');
                    url_fetch = typeof url_fetch !== undefined ? url_fetch : '';
                }
				var template =
					'<div class="redactor-modal-tab redactor-group" data-title="General">' +
					'<section id="redactor-modal-checkbox-title">' +
					'<label>URLをペスト<br><span style="font-size: 9px; color: rgba(2, 6, 6, 0.77);">※指定がない場合は使用しないでください。同サイト内の記事にリンクするときのみ使います</span></label>' +
					'<input type="text" id="url-fetch" class="form-control" value="'+ url_fetch +'"/>' +
					'</section>' +
					'<section>' +
					'<button id="redactor-modal-button-action">挿入</button>' +
					'<button id="redactor-modal-button-cancel">キャンセル</button>' +
					'</section>' +
					'</div>';

				this.modal.addTemplate('insta-template', template);
				this.modal.load('insta-template', 'URL内容取得', 600);
				this.modal.show();

                this.modal.getActionButton().click($.proxy(function(){
                    this.blockFormat.fetchUrlInsert(currentElement);
                }, this));
			},
			fetchUrlInsert: function(currentElement)
			{
				var url = $('#url-fetch').val();
				var that = this;

				$.get('/ajax/fetch-url?url='+encodeURIComponent(url), function(data){
					if(data.error !== undefined) {
						alert("ERROR CODE " + data.code);
						that.modal.close();
						return;
					}

					var contentInsert =
						'<div class="url-thumbnail">' +
						'<a href="'+url+'"><img src="'+data.thumbnail_url+'" /></a>' +
						'</div>' +
						'<div class="url-info">' +
						'<div class="url-canonical">' +
						'<a href="'+url+'">'+data.title+'</a>' +
						'</div>' +
						'<div class="url-desc">' +
						'<p>'+data.description+'</p>'+
						'</div>' +
						'<div class="url-see-more">' +
						'<a href="'+url+'">続きを読む</a>' +
						'</div>' +
						'</div>' +
						'<div class="clearfix"></div>' ;
					var templateInsert = '<p>&nbsp;</p>' +
                        '<div class="url-debugger">' +
                        contentInsert +
                        '</div>' +
                        '<p>&nbsp;</p>';
                    that.modal.close();
                    if(typeof  currentElement !== undefined && currentElement.tagName !== undefined) {
                        $(currentElement).html(contentInsert);
                        that.caret.after($(currentElement).last());
                    }else{
                        that.insert.html(templateInsert);
                        that.caret.after($('div.url-debugger').last());
					}
				});

				this.modal.getActionButton().attr('disabled', 'disabled').css('background', '#CCC').html('読込中...');
			},
			largerText: function()
			{
				this.inline.format('larger', 'class', 'larger-text');
			},
			tableOfContents: function()
			{
				var that = this;
				if(that.code.get() != ''){
					var is_insert = false;
					var templateInsert = '<div class="head_title_block"><p>目次</p><ul id="head_title_page">';
					$('.redactor-editor').find('h2').each(function(i, elm){
							 if($(elm).text() != ''){
									 is_insert = true;
									 $(elm).attr('id', (i + 1));
									 templateInsert += '<li><a href="#' + (i + 1) + '">' + $(elm).text() + '</a></li>';
							 }
					 });
					templateInsert += '</ul></div>';
					if(is_insert){
							 that.insert.html(templateInsert);

					}
				}
			},
            html: function()
			{
				if(!this.placeholder.isEditorEmpty()){
					this.placeholder.hide();
				}else{
                    this.placeholder.show();
				}
			}
		};
	};

})(jQuery);

(function($)
{
	$.Redactor.prototype.table = function()
	{
        return {
                langs: {
                    en: {
                        "table": "表",
                        "insert-table": "表を挿入",
                        "insert-row-above": "行を上に挿入",
                        "insert-row-below": "行を下に挿入",
                        "insert-column-left": "列を左に挿入",
                        "insert-column-right": "列を右に挿入",
                        "add-head": "ヘッダーを追加",
                        "delete-head": "ヘッダーを削除",
                        "delete-column": "列を削除",
                        "delete-row": "行を削除",
                        "delete-table": "表を削除"
                    }
                },
                init: function()
                {
                    var dropdown = {};

                    dropdown.insert_table = {
                        title: this.lang.get('insert-table'),
                        func: this.table.insert,
                        observe: {
                            element: 'table',
                            in: {
                                attr: {
                                    'class': 'redactor-dropdown-link-inactive',
                                    'aria-disabled': true,
                                }
                            }
                        }
                    };
                    dropdown.insert_row_above = {
                            title: this.lang.get('insert-row-above'),
                            func: this.table.addRowAbove,
                            observe: {
                                    element: 'table',
                                    out: {
                                            attr: {
                                                    'class': 'redactor-dropdown-link-inactive',
                                                    'aria-disabled': true,
                                            }
                                    }
                            }
                    };
                    dropdown.insert_row_below = {
                            title: this.lang.get('insert-row-below'),
                            func: this.table.addRowBelow,
                            observe: {
                                    element: 'table',
                                    out: {
                                            attr: {
                                                    'class': 'redactor-dropdown-link-inactive',
                                                    'aria-disabled': true,
                                            }
                                    }
                            }
                    };
                    dropdown.insert_column_left = {
                            title: this.lang.get('insert-column-left'),
                            func: this.table.addColumnLeft,
                            observe: {
                                    element: 'table',
                                    out: {
                                            attr: {
                                                    'class': 'redactor-dropdown-link-inactive',
                                                    'aria-disabled': true,
                                            }
                                    }
                            }
                    };
                    dropdown.insert_column_right = {
                            title: this.lang.get('insert-column-right'),
                            func: this.table.addColumnRight,
                            observe: {
                                    element: 'table',
                                    out: {
                                            attr: {
                                                    'class': 'redactor-dropdown-link-inactive',
                                                    'aria-disabled': true,
                                            }
                                    }
                            }
                    };
                    dropdown.add_head = {
                            title: this.lang.get('add-head'),
                            func: this.table.addHead,
                            observe: {
                                    element: 'table',
                                    out: {
                                            attr: {
                                                    'class': 'redactor-dropdown-link-inactive',
                                                    'aria-disabled': true,
                                            }
                                    }
                            }
                    };
                    dropdown.delete_head = {
                            title: this.lang.get('delete-head'),
                            func: this.table.deleteHead,
                            observe: {
                                    element: 'table',
                                    out: {
                                            attr: {
                                                    'class': 'redactor-dropdown-link-inactive',
                                                    'aria-disabled': true,
                                            }
                                    }
                            }
                    };
                    dropdown.delete_column = {
                            title: this.lang.get('delete-column'),
                            func: this.table.deleteColumn,
                            observe: {
                                    element: 'table',
                                    out: {
                                            attr: {
                                                    'class': 'redactor-dropdown-link-inactive',
                                                    'aria-disabled': true,
                                            }
                                    }
                            }
                    };
                    dropdown.delete_row = {
                            title: this.lang.get('delete-row'),
                            func: this.table.deleteRow,
                            observe: {
                                    element: 'table',
                                    out: {
                                            attr: {
                                                    'class': 'redactor-dropdown-link-inactive',
                                                    'aria-disabled': true,
                                            }
                                    }
                            }
                    };
                    dropdown.delete_table = {
                        title: this.lang.get('delete-table'),
                        func: this.table.deleteTable,
                        observe: {
                            element: 'table',
                            out: {
                                attr: {
                                    'class': 'redactor-dropdown-link-inactive',
                                    'aria-disabled': true,
                                }
                            }
                        }
                    };


                    var button = this.button.addAfter('review-box', 'table', this.lang.get('table'));
                    this.button.addDropdown(button, dropdown);
                },
                insert: function()
                {
                    if (this.table.getTable())
                    {
                        return;
                    }

                    this.placeholder.hide();

                    var rows = 2;
                    var columns = 3;
                    var $tableBox = $('<div>');
                    var $table = $('<table />');


                    for (var i = 0; i < rows; i++)
                    {
                        var $row = $('<tr>');

                        for (var z = 0; z < columns; z++)
                        {
                            var $column = $('<td>' + this.opts.invisibleSpace + '</td>');

                            // set the focus to the first td
                            if (i === 0 && z === 0)
                            {
                                $column.append(this.marker.get());
                            }

                            $($row).append($column);
                        }

                        $table.append($row);
                    }

                    $tableBox.append($table);
                    var html = $tableBox.html();

                    this.buffer.set();

                    var current = this.selection.current();
                    if ($(current).closest('li').length !== 0)
                    {
                        $(current).closest('ul, ol').first().after(html);
                    }
                    else
                    {
                        this.air.collapsed();
                        this.insert.html(html);
                    }

                    this.selection.restore();
                    this.core.callback('insertedTable', $table);
                },
                getTable: function()
                {
                    var $table = $(this.selection.current()).closest('table');

                    if (!this.utils.isRedactorParent($table))
                    {
                        return false;
                    }

                    if ($table.size() === 0)
                    {
                        return false;
                    }

                    return $table;
                },
                restoreAfterDelete: function($table)
                {
                        this.selection.restore();
                        $table.find('span.redactor-selection-marker').remove();
                },
                deleteTable: function()
                {
                    var $table = this.table.getTable();
                    if (!$table)
                    {
                        return;
                    }

                    this.buffer.set();


                    var $next = $table.next();
                    if (!this.opts.linebreaks && $next.length !== 0)
                    {
                        this.caret.start($next);
                    }
                    else
                    {
                        this.caret.after($table);
                    }
                    var jclrGrips = $table.prev('.JCLRgrips');

                    $table.remove();
                    jclrGrips.remove();
                    $(window).trigger('resize');

                },
                deleteRow: function()
                {
                        var $table = this.table.getTable();
                        if (!$table)
                        {
                                return;
                        }

                        var $current = $(this.selection.current());
                        this.buffer.set();
                        var $current_tr = $current.closest('tr');
                        var $focus_tr = $current_tr.prev().length ? $current_tr.prev() : $current_tr.next();
                        if ($focus_tr.length)
                        {
                                var $focus_td = $focus_tr.children('td, th').first();
                                if ($focus_td.length)
                                {
                                        $focus_td.prepend(this.marker.get());
                                }
                        }
                        $current_tr.remove();
                        this.table.restoreAfterDelete($table);
                        $(window).trigger('resize');
                },
                deleteColumn: function()
                {
                        var $table = this.table.getTable();
                        if (!$table)
                        {
                                return;
                        }

                        this.buffer.set();
                        var $current = $(this.selection.current());
                        var $current_td = $current.closest('td, th');
                        var index = $current_td[0].cellIndex;
                        $table.find('tr').each($.proxy(function(i, elem)
                        {
                                var $elem = $(elem);
                                var focusIndex = index - 1 < 0 ? index + 1 : index - 1;
                                if (i === 0)
                                {
                                        $elem.find('td, th').eq(focusIndex).prepend(this.marker.get());
                                }
                                $elem.find('td, th').eq(index).remove();
                        }, this));
                        this.table.restoreAfterDelete($table);
                        $(window).trigger('resize');
                },
                addHead: function()
                {
                        var $table = this.table.getTable();
                        if (!$table)
                        {
                                return;
                        }

                        var invisibleSpace = this.opts.invisibleSpace;
                        this.buffer.set();
                        if ($table.find('thead').size() !== 0)
                        {
                                this.table.deleteHead();
                                return;
                        }
                        var tr = $table.find('tr').first().clone();

                        tr.find('td').each(function () {
                          var tdStyle = $(this).attr('style');

                          $(this).replaceWith($('<th style="' + tdStyle + '">').html(invisibleSpace));
                        });
                        $thead = $('<thead></thead>').append(tr);
                        $table.prepend($thead);
                },
                deleteHead: function()
                {
                        var $table = this.table.getTable();
                        if (!$table)
                        {
                                return;
                        }

                        var $thead = $table.find('thead');
                        if ($thead.size() === 0)
                        {
                                return;
                        }
                        this.buffer.set();
                        $thead.remove();
                },
                addRowAbove: function()
                {
                        this.table.addRow('before');
                },
                addRowBelow: function()
                {
                        this.table.addRow('after');
                },
                addColumnLeft: function()
                {
                        this.table.addColumn('before');
                },
                addColumnRight: function()
                {
                        this.table.addColumn('after');
                },
                addRow: function(type)
                {
                        var $table = this.table.getTable();
                        if (!$table)
                        {
                                return;
                        }

                        this.buffer.set();
                        var $current = $(this.selection.current());
                        var $current_tr = $current.closest('tr');
                        var new_tr = $current_tr.clone();
                        new_tr.find('th').replaceWith(function()
                        {
                                var $td = $('<td>');
                                $td[0].attributes = this.attributes;
                                return $td.append($(this).contents());
                        });
                        new_tr.find('td').html(this.opts.invisibleSpace);
                        if (type === 'after')
                        {
                                $current_tr.after(new_tr);
                        }
                        else
                        {
                                $current_tr.before(new_tr);
                        }
                },
                addColumn: function (type)
                {
                        var $table = this.table.getTable();
                        if (!$table)
                        {
                                return;
                        }

                        var index = 0;
                        var current = $(this.selection.current());
                        this.buffer.set();
                        var $current_tr = current.closest('tr');
                        var $current_td = current.closest('td, th');
                        $current_tr.find('td, th').each($.proxy(function(i, elem)
                        {
                                if ($(elem)[0] === $current_td[0])
                                {
                                        index = i;
                                }
                        }, this));
                        $table.find('tr').each($.proxy(function(i, elem)
                        {
                                var $current = $(elem).find('td, th').eq(index);
                                var td = $current.clone();
                                td.html(this.opts.invisibleSpace);
                                if (type === 'after')
                                {
                                        $current.after(td);
                                }
                                else
                                {
                                        $current.before(td);
                                }
                        }, this));
                }
            };
        };
})(jQuery);
var isFocusOnReviewBox = false;
$(document).ready(function(){
    $(document).on('mouseenter', 'div.review-box-control', function(){
        isFocusOnReviewBox = true;
    }).on('mouseleave', 'div.review-box-control', function(){
        isFocusOnReviewBox = false;
        $('div.review-box-control').remove();
        $('.background-hover').removeClass('background-hover');
    });
    $(document).on('mouseenter','div.redactor-editor div.reviewbox,div.redactor-editor div.checkbox,' +
		'div.redactor-editor div.qa,div.redactor-editor div.conv, div.insert-from-html-box, ' +
		'div.redactor-editor div.url-debugger,div.redactor-editor>h2,div.redactor-editor p>h2,' +
		'div.redactor-editor>h3,div.redactor-editor p>h3,div.redactor-editor>h4,div.redactor-editor p>h4,div.head_title_block,' +
        'div.redactor-editor div:not(.reviewbox,.checkbox,.qa,.conv,.url-debugger)>h3, div.redactor-editor div:not(.reviewbox,.checkbox,.qa,.conv,.url-debugger)>h2'+
		'div.insert-from-html-box div:not(.tog_review)'
        ,function(event){
        event.preventDefault();
        isFocusOnReviewBox = true;
        $('div.review-box-control').remove();
        $('.background-hover').removeClass('background-hover');
		eml_box = this;
        $(this).attr('contenteditable', 'false');
        $(this).addClass('background-hover');
        var html_control = '';
        var c_top = $(this).offset().top ;
        var c_left = $(this).offset().left;
        var copy_elm = '<i class="ion-ios-copy-outline quick-control" title="コピー" onclick="coppyReviewBox(eml_box)"> コピー</i>';
        if($(this).hasClass('reviewbox')){
            c_top += $(this).height() + 28;
            html_control += '<i class="ion-compose quick-control" title="編集" onclick="modalReviewBox(that_redactor, eml_box);"> 編集</i>';
        }else if($(this).hasClass('qa')) {
            c_top += $(this).height() + 15;
            $(this).find('*').addClass('background-hover');
            html_control += '<i class="ion-compose quick-control" title="編集" onclick="instanceRedactor.blockFormat.qaBox(eml_box);"> 編集</i>';
        }else if($(this).hasClass('conv')) {
            c_top += $(this).height();
            html_control += '<i class="ion-compose quick-control" title="編集" onclick="instanceRedactor.chatbox.chatbox(eml_box);"> 編集</i>';
		} else if($(this).hasClass('url-debugger')) {
            c_top += $(this).height() + 8;
            html_control += '<i class="ion-compose quick-control" title="編集" onclick="instanceRedactor.blockFormat.fetchUrl(eml_box);"> 編集</i>';
		} else if($(this).hasClass('rank')) {
            c_top += $(this).height() + 3;
            $(this).find('*').addClass('background-hover');
            html_control += '<i class="ion-compose quick-control" title="編集" onclick="instanceRedactor.blockFormat.rankingTitle(eml_box);"> 編集</i>';
		}else if($(this).hasClass('checkbox')){
            c_top += $(this).height()+ 15;
            html_control += '<i class="ion-compose quick-control" title="編集" onclick="modalCheckbox(that_redactor, eml_box);"> 編集</i>';
        }else if($(this).hasClass('head_title_block')){
			c_top += $(this).height()+ 20;
            copy_elm = '';
		}else if($(this).hasClass('insert-from-html-box')) {
            c_top += $(this).height();
            html_control += '<i class="ion-compose quick-control" title="編集" onclick="instanceRedactor.htmlBox.show(eml_box);"> 編集</i>';
        }else {
            c_top += $(this).height();
            $(this).is('H3') ? c_top += 35 : '';
            $(this).removeAttr('contenteditable');
        }
        html_control += '<i class="ion-android-delete quick-control" title="削除" onclick="delReviewBox(eml_box);"> 削除</i>'+
                            '<i class="ion-arrow-up-c quick-control" title="上行追加" onclick="addNewLine(eml_box, \'up\')"> 上行追加</i>'+
                            '<i class="ion-arrow-down-c quick-control" title="下行追加" onclick="addNewLine(eml_box, \'down\')"> 下行追加</i>'+
            				copy_elm;
        c_top = 'top:' + c_top + 'px;';
        c_left = 'left: ' + c_left + 'px;';
        html_control = '<div class="review-box-control" style="'+ c_top + c_left +'">'+ html_control +'</div>';
        $('.redactor-link-tooltip').remove();
        $('body').append(html_control);
    }).on('mouseleave', 'div.redactor-editor>div.reviewbox,div.redactor-editor>div.checkbox,' +
        'div.redactor-editor>div.qa,div.redactor-editor>div.conv,' +
        'div.redactor-editor>div.url-debugger,div.redactor-editor>h2,' +
        'div.redactor-editor>h3,div.redactor-editor>h4,div.head_title_block, div.insert-from-html-box',function(){
        isFocusOnReviewBox = false;
        setTimeout(function(){
            if(isFocusOnReviewBox){
                return;
            }
            $('div.review-box-control').remove();
            $('.background-hover').removeClass('background-hover');
		}, 5);
    }).on('click','div.redactor-editor>h3.rank, div.redactor-editor>h2.rank',function(event){
        event.preventDefault();
        $('.redactor-link-tooltip').remove();
	});
});

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function getCookie(cname){
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {

            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function delReviewBox(elm){
    $.confirm({
        text: "本当に削除してよろしいでしょうか？",
        confirmButton: "はい",
        cancelButton: "いいえ",
        confirm: function() {
            $(elm).find('span.review-box-control').remove();
            $(elm).remove();
        },
        cancel: function() {
            return false
        }
    });
}
function addNewLine(elm, event){
    $(elm).find('span.review-box-control').remove();
    if(event == 'up'){
        $(elm).before('<p>&#8203;</p>');
    }else{
        $(elm).after('<p>&#8203;</p>');
    }
}

function coppyReviewBox(elm, event){
    $(elm).find('span.review-box-control').remove();
    $(elm).after($(elm).clone());
}

(function($)
{
    $.Redactor.prototype.iconic2 = function()
    {
        return {
            init: function ()
            {
                var icons = {
                    'ranking': '<i class="fa fa-sort-numeric-asc"></i>',
                    'red-marker' : '<i class="fa fa-font"></i>' ,
                    'yellow-marker' : '<i class="fa fa-h-square"></i>' ,
                    'horizontalrule' : '<i class="glyphicon glyphicon-minus"></i>' ,
                    'quote' : '<i class="fa fa-quote-left"></i>' ,
                    'review-box' : '<i class="fa fa-star-half-full"></i>' ,
                    'table' : '<i class="fa fa-table"></i>' ,
                    'lists' : '<i class="fa fa-list-ol"></i>' ,
                    'checkbox' : '<i class="fa fa-check-square-o"></i>' ,
                    'chatbox-btn' : '<i class="fa fa-wechat"></i>' ,
                    'image' : '<i class="fa fa-photo"></i>' ,
                    'gphoto' : '<i class="fa fa-cloud"></i>' ,
                    'twitter' : '<i class="fa fa-twitter-square"></i>' ,
                    'instagram' : '<i class="fa fa-instagram"></i>' ,
                    'video' : '<i class="fa fa-youtube-square"></i>',
                    'link' : '<i class="fa fa-link"></i>',
                    'product' : '<i class="fa fa-shopping-cart"></i>',
                    'fetch_url' : '<i class="fa fa-paperclip"></i>',
                    'qabox' : '<i class="fa fa-question-circle"></i>',
                    'table_of_contents' : '<i class="fa fa-table-of-contents"></i>',
                    'html' : '<i class="fa fa-html"></i>',
                    'center' : '<i class="fa fa-center"></i>',
                };
 
                $.each(this.button.all(), $.proxy(function(i,s)
                {
                    var key = $(s).attr('rel');
                    if (typeof icons[key] !== 'undefined')
                    {
                        var icon = icons[key];
                        var button = this.button.get(key);
                        this.button.setIcon(button, icon);
                    }
 
                }, this));
            }
        };
    };
})(jQuery);

