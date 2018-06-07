    (function($){  
        function R4() {  
            return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);  
        };  
          
        function createId() {  
            return (R4() + R4() + "-" + R4() + "-" + R4() + "-" + R4() + "-" + R4() + R4() + R4());  
        };  
          
        function createUploadIframe(frameId,uri){  
            var iframeHtml = '<iframe id="' + frameId + '" name="' + frameId + '" style="position:absolute; top:-9999px; left:-9999px"';  
              
            if(window.ActiveXObject) {  
                //修复IE9的BUG  
                if($.browser.version=="9.0") {  
                    io = document.createElement('iframe');  
                    io.id = frameId;  
                    io.name = frameId;  
                }else if($.browser.version=="6.0"||$.browser.version=="7.0"||$.browser.version=="8.0"){  
                    var io = document.createElement('<iframe id="' + frameId + '" name="' + frameId + '" />');  
                    if(typeof uri == 'boolean'){  
                        io.src = 'javascript:false';  
                    }else if(typeof uri == 'string'){  
                        io.src = uri;  
                    }  
                }  
            }  
            iframeHtml += ' />';  
            $(iframeHtml).appendTo(document.body);  
            return $('#' + frameId).get(0);  
        };  
          
        function createUploadForm(formId,files,data) {  
            var form = $('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');  
            if (data) {  
                for (var i in data) {  
                    $('<input type="hidden" name="' + i + '" value="' + data[i] + '" />').appendTo(form);  
                }  
            }  
              
            for(var i=0;i<files.length;i++){  
                var ele = files[i];  
                var oldElement = (typeof ele == "string") ? $('#' + ele) : ele;  
                var fileId = 'jUploadFile-' + createId();  
                var newElement = $(oldElement).clone();  
                $(oldElement).attr('id', fileId);  
                $(oldElement).before(newElement);  
                $(oldElement).appendTo(form);  
            }  
      
            //set attributes  
            $(form).css('position', 'absolute');  
            $(form).css('top', '-1200px');  
            $(form).css('left', '-1200px');  
            $(form).appendTo('body');  
            return form;  
        };  
          
        //修复Jquery的新版本没有这个方法的问题  
        function handleError(s,xhr,status,e) {  
            if (s.error){  
                s.error.call(s.context || s,xhr,status,e);  
            }  
            if (s.global){  
                (s.context ? $(s.context) : $.event).trigger("ajaxError",[xhr, s, e]);  
            }  
        };  
      
      
        function uploadHttpData(r, type) {  
            var data = !type;  
            data = type == "xml" || data ? r.responseXML : r.responseText;  
              
            if (type == "script") $.globalEval(data);  
              
            // Get the JavaScript object, if JSON is used.  
            if (type == "json") {  
                //修复回调JSON的问题  
                var data = r.responseText;  
                if (data.match(/^<pre/i)) {  
                    data = data.substring(data.indexOf('>') + 1, data.length - 6);  
                }  
                eval("data=" + data);  
            }  
            if (type == "html") $("<div>").html(data).evalScripts();  
            return data;  
        };  
          
          
        $.ajaxFileUpload = function (s){  
            s = $.extend({},$.ajaxSettings,s);  
            var id = createId();  
              
            var frameId = 'jUploadFrame-' + id;  
            var formId = 'jUploadForm-' + id;  
              
            var form = createUploadForm(formId, s.files,(typeof(s.data) == 'undefined' ? false : s.data));  
            var io = createUploadIframe(frameId, s.secureuri);  
            // Watch for a new set of requests  
            if (s.global && !$.active++) $.event.trigger("ajaxStart");  
              
            var requestDone = false;  
            // Create the request object  
            var xml = {};  
            if (s.global) $.event.trigger("ajaxSend", [xml,s]);  
            // Wait for a response to come back  
              
              
            var uploadCallback = function(isTimeout) {  
                var io = document.getElementById(frameId);  
                  
                try {  
                    var doc = io.contentWindow ? io.contentWindow.document : io.contentDocument.document;  
                    xml.responseText = doc.body.innerHTML;  
                    xml.responseXML = doc.XMLDocument;  
                } catch (e) {  
                    handleError(s, xml, null, e);  
                }  
                  
                if (xml || isTimeout == "timeout") {  
                    requestDone = true;  
                    var status;  
                    try {  
                        status = (isTimeout != "timeout") ? "success" : "error";  
                        // Make sure that the request was successful or notmodified  
                          
                        if (status != "error") {  
                            // process the data (runs the xml through httpData regardless of callback)  
                            var data = uploadHttpData(xml,s.dataType);  
                              
                            // If a local callback was specified, fire it and pass it the data  
                            if (s.success) s.success(data, status);  
                            // Fire the global callback  
                            if (s.global) $.event.trigger("ajaxSuccess", [xml, s]);  
                        } else  
                            handleError(s, xml, status);  
                    } catch (e) {  
                        status = "error";  
                        handleError(s, xml, status, e);  
                    }  
      
                    // The request was completed  
                    if (s.global) $.event.trigger("ajaxComplete", [xml, s]);  
      
                    // Handle the global AJAX counter  
                    if (s.global && !--$.active) $.event.trigger("ajaxStop");  
      
                    // Process result  
                    if (s.complete) s.complete(xml, status);  
      
                    $(io).unbind();  
      
                    setTimeout(function () {  
                        try {  
                            $(io).remove();  
                            $(form).remove();  
                        } catch (e) {  
                            handleError(s, xml, null, e);  
                        }  
                    }, 100);  
                    xml = null;  
                }  
            };  
              
              
            if (s.timeout > 0) {  
                setTimeout(function () {  
                    // Check to see if the request is still happening  
                    if (!requestDone) uploadCallback("timeout");  
                }, s.timeout);  
            };  
      
            try {  
                var form = $('#' + formId);  
                $(form).attr('action', s.url);  
                $(form).attr('method', 'post');  
                $(form).attr('target', frameId);  
      
                  
                if (form.encoding) {  
                    $(form).attr('encoding', 'multipart/form-data');  
                } else {  
                    $(form).attr('enctype', 'multipart/form-data');  
                }  
                $(form).submit();  
            } catch (e) {  
                handleError(s, xml, null, e);  
            }  
            $('#' + frameId).load(uploadCallback);  
            return {  
                abort : function(){}  
            };  
        };  
          
    })(jQuery);  

