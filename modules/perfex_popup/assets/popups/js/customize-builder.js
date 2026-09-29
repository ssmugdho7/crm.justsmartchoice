(function($) {
    "use strict"; // Start of use strict


    var editor = grapesjs.init({
        height: '100%',
        styleManager: {
            sectors: [
                {
                    name: 'Layout',
                    open: false,
                    properties: ['margin', 'padding', 'width', 'height', 'max-width', 'min-height'],
                },
                {
                  name: 'Typography',
                  open: false,
                  properties: [
                    "font-family", "color", "font-size", "font-weight", "letter-spacing", "line-height", "text-align", "text-shadow"
                  ],
                },
                {
                    name: 'Background',
                    open: false,
                    buildProps: ["background-color", "background"]
                },
                {
                  name: 'Border & Shadow',
                  open: false,
                  properties: ["box-shadow", "border", "border-radius"],
                },
              ],
        },
        storageManager: {
            type: 'remote',
            stepsBeforeSave: 5,
            urlStore: urlStore,
            urlLoad: urlLoad,
            params: {
                [csrfName]: csrfHash
            },
        },
        container: '#gjs',
        fromElement: true,
        assetManager: {
            multiUpload: false,
            upload: upload_Image,
            params: {
                [csrfName]: csrfHash
            },
            uploadName: 'files',
            assets: images_url,
        },
        plugins: ['gjs-preset-webpage'],
    });
   
    editor.on('storage:start:store', (e) => {
        // change contentype for codeigniter csrf
        editor.StorageManager.get('remote').set({ contentTypeJson: false, headers: { 'X-Requested-With': "XMLHttpRequest" } }); 
    });

    // submit form resize popup
    const functionRSBRFormResizePopup = function() {

        $("#rsbr-button-save").attr("disabled", true);
        var values = $(this).serialize();
        var form = $(this);
        $.ajax({
            async: false,
            url: resize_popup_url,
            type: 'POST',
            data: values + '&'+csrfName+'=' + csrfHash,
            beforeSend: function() {},
            success: function(res) {
                var html = "";
                if ($.isEmptyObject(res.error)) {
                    $(".gjs-frame-wrapper").css({
                        width: res.data.width + 'px',
                        height: res.data.height + 'px'
                    });
                    html = '<i class="fa fa-check-circle text-success"></i><small> ' + res.success + '</smal>';
                } else {
                    html = '<i class="fa fa-times-circle text-error"></i><small> ' + res.error + '</smal>';
                }
                Swal.fire({
                    position: 'top-end',
                    timer: 3000,
                    toast: true,
                    html: html,
                    showConfirmButton: false,
                });
            },
            error: function(xhr, ajaxOptions, thrownError) {
                console.log(xhr);
            }
        });

        $("#rsbr-button-save").removeAttr("disabled");
        return false;
    };
    const functionChangeTemplate = function() {
        var code = $(this).attr("data-code");
        var type_page = $(this).attr("data-type-page");
        if (code) {
            $.ajax({
                type: "POST",
                url: url_load_template + "/" + code + "/" + type_page,
                data: {
                    [csrfName]: csrfHash,
                },
                beforeSend: function() {
                    $('#loadingMessage').css('display', 'block')
                },
                success: function(response) {
                    $('#loadingMessage').css('display', 'none');
                    modal_templates.style.display = "none";
                    if (response.error !== undefined) {
                        var html = '<i class="fa fa-times-circle text-error"></i><small> ' + response.error + '</smal>';
                        Swal.fire({
                            position: 'top-end',
                            timer: 3000,
                            toast: true,
                            html: html,
                            showConfirmButton: false,
                        });

                    } else {
                        editor.setComponents(response.content);
                        editor.setStyle(response.style);
                    }
                    
                }
            });
        }
        return false;
    };

    $('#rsbr_form_resize_popup').on('submit', functionRSBRFormResizePopup);
    $('.card-template').on('click', functionChangeTemplate);

    // Show button page
    var btnPage = document.querySelector('.btn-page-group');
    var btnPanel = document.querySelector('.gjs-pn-commands');
    btnPanel.appendChild(btnPage);
    

    // add default css on template
    const head = editor.Canvas.getDocument().head;
    head.insertAdjacentHTML('beforeend', `
             <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=${google_fonts_string}&display=swap">
              <link rel="stylesheet" href="${url_default_css_template}">
              
              <style type="text/css">
              </style>
            `);

    editor.on('asset:remove', (response) => {
        var src = response.get('src');
        var data = {
            [csrfName]: csrfHash,

            image_src: src
        };
        $.ajax({
            url: url_delete_image,
            type: 'POST',
            data: data,
        });
    });


    var pn = editor.Panels;
    [
        ['open-sm', 'Style Manager'],
        ['open-layers', 'Layers'],
        ['open-blocks', 'Blocks']
    ]
    .forEach(function(item) {
        pn.getButton('views', item[0]).set('attributes', {
            title: item[1],
            'data-tooltip-pos': 'bottom'
        });
    });


    // Show borders by default
    //pn.getButton('options', 'sw-visibility').set('active', 1);
    // Do stuff on load
    editor.on('load', function() {
        var $ = grapesjs.$;
        document.getElementById('gjs-clm-label').innerHTML = 'Events';
        var selectobject = document.getElementById("gjs-clm-states");
        for (var i = 0; i < selectobject.length; i++) {
            if (selectobject.options[i].text == 'Even/Odd')
                selectobject.remove(i);
        }
        // Make private already inserted selectors
        editor.SelectorManager.getAll().each(selector => selector.set('private', 1));

        // All new selectors will be private
        editor.on('selector:add', selector => selector.set('private', 1));

        // Load and show settings and style manager
        var openTmBtn = pn.getButton('views', 'open-tm');
        openTmBtn && openTmBtn.set('active', 1);
        var openSm = pn.getButton('views', 'open-sm');
        openSm && openSm.set('active', 1);

        // Add Settings Sector
        var traitsSector = $('<div class="gjs-sm-sector no-select">' +
            '<div class="gjs-sm-sector-title"><div class="gjs-sm-sector-caret"><svg viewBox="0 0 24 24"><path fill="currentColor" d="M7,10L12,15L17,10H7Z"></path></svg></div><div class="gjs-sm-sector-label">Settings</div></div>' +
            '<div class="gjs-sm-properties" style="display: none;"></div></div>');
        var traitsProps = traitsSector.find('.gjs-sm-properties');
        traitsProps.append($('.gjs-trt-traits'));
        $('.gjs-sm-sectors').before(traitsSector);
        traitsSector.find('.gjs-sm-sector-title').on('click', function() {
            var traitStyle = traitsProps.get(0).style;
            var hidden = traitStyle.display == 'none';
            if (hidden) {
                traitStyle.display = 'block';
            } else {
                traitStyle.display = 'none';
            }
        });

        editor.Panels.getButton('options', 'sw-visibility').set('active', true);


        var listFontProperty = [];
        all_fonts.forEach(function(item) {
            listFontProperty.push({ id:  `'${item}', sans-serif `, label: item });
        });
        // change font typography default
        let fontProperty = editor.StyleManager.getProperty('typography','font-family');
        fontProperty.setOptions(listFontProperty);

        editor.Panels.addPanel({
            id: 'myNewPanel',
            visible: true,
            content: `
                        <div class="left-panel-builder">
                            <button id="change_templates" class='btn btn-light'>${langs.changeTemplates}</button>
                            <button id="resize_popup" class='btn btn-success'>${langs.resize_popup}</button>
                        </div>`
        });
        //Resize popup event
        $('#resize_popup').on('click', function() {
            modal_resize.style.display = "block";
        });
        $('#change_templates').on('click', function() {
            modal_templates.style.display = "block";
        });

        // Open block manager
        var openBlocksBtn = editor.Panels.getButton('views', 'open-blocks');
        openBlocksBtn && openBlocksBtn.set('active', 1);
        const assetManager = editor.AssetManager;
        editor.on('asset:upload:response', (response) => {

            if (response.error !== undefined) {
                var html = '<i class="fa fa-times-circle text-error"></i><small> ' + response.error + '</smal>';
                Swal.fire({
                    position: 'top-end',
                    timer: 3000,
                    toast: true,
                    html: html,
                    showConfirmButton: false,
                });

            } else {
                assetManager.add(response);
            }

        });
    });
    // Fix XSS
    var iframeBody = editor.Canvas.getBody();
    $(iframeBody).on("paste", '[contenteditable="true"]', function(e) {
        e.stopPropagation();
        e.preventDefault();
        var old_text = e.originalEvent.clipboardData.getData('text');
        const parser = new DOMParser();
        var new_text = parser.parseFromString(old_text, 'text/html').body.firstChild.textContent;
        e.target.ownerDocument.execCommand("insertText", false, new_text);
    });
    /* end load editor */
    editor.on('storage:end:load', (e) => {
        $('#loadingMessage').css('display', 'none');
    });

    // Collapsed Category in Blocks 
    const categories = editor.BlockManager.getCategories();
    categories.each(category => {
        category.set('open', false).on('change:open', opened => {
            opened.get('open') && categories.each(category => {
                category !== opened && category.set('open', false)
            })
        })
    })

    // Resize popup event
    var modal_resize = document.getElementById("modalResize");
    var modal_templates = document.getElementById("modalTemplates");
    $('#modalTemplatesClose').on('click', function(event) {
        modal_templates.style.display = "none";
    });
    $('#modalResizeClose').on('click', function(event) {
        modal_resize.style.display = "none";
    });

    window.onclick = function(event) {
        if (event.target == modal_resize)
            modal_resize.style.display = "none";
        else if (event.target == modal_templates)
            modal_templates.style.display = "none";
    }

    // end resize popup event


    $("#save-builder").on("click", function(e) {
        editor.store(function(res) {
            var html = "";
            res = JSON.parse(res);
            if ($.isEmptyObject(res.error)) {
                html = '<i class="fa fa-check-circle text-success"></i><small> ' + res.success + '</smal>';
            } else {
                html = '<i class="fa fa-times-circle text-error"></i><small> ' + res.error + '</smal>';
            }
            Swal.fire({
                position: 'top-end',
                timer: 3000,
                toast: true,
                html: html,
                showConfirmButton: false,
            });
        });
    });

    if ($(window).width() <= 768) {
        $('#loadingMessage').css('display', 'none');
        $('#mobileAlert').css('display', 'block');
    }


    $("#back-button").on("click", function(e) {
        window.location.href = back_button_url;

    });
    $("#publish-builder").on("click", function(e) {
        window.location.href = publish_button_url;
    });

    

})(jQuery); // End of use strict  