// ==================== #region Popup ==================== //
//Handle message common
"use strict";
class PolyMessage {
    /**
     * Display Popup
     * @param {string} iconType iconType encompasses values like success, warning, info, question, error by sweetalert2
     * @param {string} messageContent is the string content to be displayed
     */
    static displayNotification(iconType, messageContent) {
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: false,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        Toast.fire({
            icon: iconType,
            title: messageContent
        });
    }

    static displayMessage(message) {
        return message ? `<p class="poly-help-message">${message}</p>` : '';
    }
}

//Handle message common
class PolyPopup {
    /**
     * Display Popup Confirm
     * @param {object[]} response An array of JSON objects. 
     * @param {boolean} isConfirmReload A boolean indicating whether reload is active. Default is false
     */
    static popup(response, isConfirmReload = false) {
        switch (response.code) {
            case 200: {
                var popup = Swal.fire({
                    title: response.title,
                    text: response.message,
                    icon: "success",
                    showCancelButton: false,
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "Ok"
                });

                if (isConfirmReload == true) {
                    popup.then((result) => {
                        if (result.isConfirmed && response.link_redirect?.trim()) {
                            window.location.href = response.link_redirect;
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    popup.then((result) => {
                        if (result.isConfirmed && response.link_redirect && response.link_redirect !== '') {
                            window.location.href = response.link_redirect;
                        }
                    });

                }

                break;
            }
            case 409:
                Swal.fire({
                    title: response.title,
                    text: response.message,
                    icon: "warning",
                    showCancelButton: false,
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "Ok"
                });
                break;
            case 500:
                Swal.fire({
                    title: response.title,
                    text: response.message,
                    icon: "warning",
                    showCancelButton: false,
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "Ok"
                });
                break;

            default:
                break;
        }
    }
}
// ==================== #region Popup ==================== //

// ==================== #region handle events ==================== //
class PolyHandleEvents {
    /**
     * Popup confirmation and execute function after confirmation.
     * @param {function} callbackFunction The function to be executed.
     * @param {string[]} argsArray Array of variables.
     * @param {object} messageObject header, message
     */
    static doConfirm(callbackFunction, argsArray, messageObject = '') {
        Swal.fire({
            title: (messageObject != '') ? messageObject.header : 'Delete',
            text: (messageObject != '') ? messageObject.message : 'Are you sure?',
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: (messageObject != '') ? messageObject.cancel : 'Cancel',
            confirmButtonText: (messageObject != '') ? messageObject.confirm : 'OK',
            allowOutsideClick: false,
        }).then((result) => {
            if (result.isConfirmed) {
                callbackFunction(...argsArray);
            }
        });
    }

    /**
     * Handle the keypress event of objects within the array inputArray.
     * @param {string[]} inputArray Array of elements class, id
     */
    static InputFriendly(inputArray) {
        inputArray.forEach(function (element) {
            $(element + '[type="text"]').on('input', function () {
                var value = $(this).val().toLowerCase().replace(/[^a-z0-9\s-_]/g, '').replace(/\s+/g, '-');
                $(this).val(value);
            });
        });
    }

    /**
     * Handle the keypress Ctrl+S
     * @param {event} e key event
     * @param {string} strElsClass the string of CSS class names of the element triggering the event. Default: .btn.btn-primary
     */
    static handleCtrlS = (e) => {
        var pressedKey = e.key.toLowerCase();
        if (pressedKey === 's') {
            e.preventDefault();
            let acceptPattern = PolyOperationFunctions.isAcceptHotkeyCtrlS();
            if (acceptPattern !== null) {
                var bottomToolbarBtn = $(acceptPattern.submit);
                if (bottomToolbarBtn.length !== 0) {
                    if (acceptPattern.pattern.indexOf('projects/project') > 0) {
                        localStorage.setItem('previousPage', 'true');
                    }
                    bottomToolbarBtn.click();

                }
            }
        }
    }
}
// ==================== #region handle events ==================== //

// ==================== #region Function ==================== //
class PolyWidgets{
    static DynamicWidget = (widget_area)=>{
        return `#widget_${widget_area}`;
    }
}

class PolyCustomMenu{
    static Init = () => {
        /**
         * The features are being integrated in version 1.0.8
            * Include custom link in primary or client*specific menus.
            * Display client*exclusive link upon authentication.
            * Target link visibility to individual clients.
            * Attach badges to administrative menu options.
            * Embed internal/external links in menus.
            * Launch links in new tabs/iFrames.
            * Restrict custom link visibility by role.
            * Limit custom link access to designated staff.
            * Control module access through staff permissions.
        */
    }
}

class PolyCommon {
    static PurseContent = (content) => {
        if (!content) return '';

        content = PolyCommon.PurseScripts(content);
        content = content.replace(/<\/?[^>]+(>|$)/g, "");
        content = content.trim();
        content = content.replace(/\s\s+/g, ' ').replace(/\n\n+/g, '\n');
        return content;
    }

    static PurseScripts = (content) => {
        if (!content) return '';

        content = content.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, '');
        content = content.trim();
        content = content.replace(/\s\s+/g, ' ').replace(/\n\n+/g, '\n');
        return content
    }
    //Common
    static displayContentBySentences(dialog_element, sentences, index = 0, sentences_element = null) {
        if (sentences.length == 0) sentences = ['Hello!'];
        var index = index;

        if (index == 0) {
            sentences.forEach(element => {
                dialog_element.append(`<span>${element}</span>`);
            });
            sentences_element = dialog_element.find('span');
        }

        if (index < sentences_element.length) {
            var currentSpan = sentences_element.eq(index);
            currentSpan.fadeIn(500, function () {
                setTimeout(function () {
                    if (index < sentences_element.length - 1) {
                        currentSpan.fadeOut(500, function () {
                            index++;
                            PolyCommon.displayContentBySentences(dialog_element, sentences, index, sentences_element);
                        });
                    }
                }, 1500);
            });
        }
    }

    static isElement(element) {
        return $('body').find(element).length > 0;
    }
    static select(label, element_name, arr, default_value, group_class) {
        let str = '';
        arr.forEach(element => {
            str += `<option value="${element}"${((element === default_value) ? ' selected' : '')}>${element}</option>`;
        });
        return `<div class="${group_class}"><label>${label}</label><select name="${element_name}" id="${element_name}" class="form-control">${str}</select></div>`;
    }

    //Contacts
    /**
    * Returns the last numeric character in a URL
    * @param {string} url The link to be processed
    * @returns The last numeric character or null
    */
    static getLastNumberFromUrl(url) {
        var idx = url.indexOf('?');
        var temp_url = idx !== -1 ? url.substring(0, idx) : url;
        var matches = temp_url.match(/\d+$/);
        if (matches) {
            return parseInt(matches[0], 10);
        }
        return null;
    }

    /**
    * Count the occurrences of the search string within the content
    * @param {string} content The content to be checked
    * @param {string} search_string The string to count occurrences
    * @returns The number of occurrences
    */
    static countStringOccurrences(content, search_string) {
        var regex = new RegExp(search_string, 'g');
        var matches = content.match(regex);
        return matches ? matches.length : 0;
    }

    /**
     * Display elements within "elements" that have a certain "pattern" and display them based on the state of "isState"
     * @param {Array} elements array of elements
     * @param {string} pattern the string to be checked
     * @param {Boolean} isState true/false
     */
    static setDisplayBasedOnPattern(elements, pattern, isState) {
        elements.each(function () {
            var content = $(this).html();
            if (content && content.indexOf(pattern) !== -1) {
                $(this).css('display', isState ? '' : 'none');
            }
        });
    }

    static extractNumber(inputString) {
        var match = inputString.match(/\d+/);
        return match ? parseInt(match[0]) : null;
    }

    //Projects
    static extractProjectIdByUrl(url) {
        const regex = /\/view\/(\d+)/;
        const match = url.match(regex);

        if (match && match[1]) {
            return match[1];
        }
        return null;
    }

    static getValueByKey(array, searchKey) {
        if (!array) return undefined;
        for (let i = 0; i < array.length; i++) {
            if (array[i].key === searchKey) {
                return array[i].value;
            }
        }
        return undefined;
    }

    /**
    * Generate a random string to be used as an ID based on the current time information.
    * @returns {string} A random string generated from current time data.
    */
    static generateUniqueID() {
        var uniqueID = Date.now().toString(36) + Math.random().toString(36).substr(2);
        return uniqueID;
    }

    /**
     * Get object by property name & value
     * @param {Array} array Array of objects
     * @param {string} field string property name
     * @param {any} value value of property name
     * @returns object by property name & value
     */
    static getObjectByField(array, field, value) {
        return array.find(item => item[field] === value);
    }

    /**
     * Get an array of string names by searchKeywords
     * @param {string} searchKeywords the search keyword string
     * @param {object[]} classes a list array of string names for the FontAwesome library's icon classes
     * @returns a list array of string names by searchKeywords
     */
    static fontAwesomeBySearchKeywords(searchKeywords, classes) {
        var restFilter = classes.filter(function (iconClass) {
            return iconClass.toLowerCase().includes(searchKeywords);
        });
        return restFilter;
    }
    /**
     * Get an array of string names for the fontAwesome
     * @returns a list array of string names for icon classes
     */
    static fontAwesome() {
        return [
            "fa-solid fa-house fa-fw",
            "fa-solid fa-magnifying-glass fa-fw",
            "fa-solid fa-user fa-fw",
            "fa-regular fa-user fa-fw",
            "fa-solid fa-check fa-fw",
            "fa-solid fa-download fa-fw",
            "fa-solid fa-image fa-fw",
            "fa-regular fa-image fa-fw",
            "fa-solid fa-phone fa-fw",
            "fa-solid fa-bars fa-fw",
            "fa-solid fa-envelope fa-fw",
            "fa-regular fa-envelope fa-fw",
            "fa-solid fa-star fa-fw",
            "fa-regular fa-star fa-fw",
            "fa-solid fa-location-dot fa-fw",
            "fa-solid fa-music fa-fw",
            "fa-solid fa-wand-magic-sparkles fa-fw",
            "fa-solid fa-heart fa-fw",
            "fa-regular fa-heart fa-fw",
            "fa-solid fa-arrow-right fa-fw",
            "fa-solid fa-circle-xmark fa-fw",
            "fa-regular fa-circle-xmark fa-fw",
            "fa-solid fa-bomb fa-fw",
            "fa-solid fa-poo fa-fw",
            "fa-solid fa-camera-retro fa-fw",
            "fa-solid fa-xmark fa-fw",
            "fa-solid fa-cloud fa-fw",
            "fa-solid fa-comment fa-fw",
            "fa-regular fa-comment fa-fw",
            "fa-solid fa-caret-up fa-fw",
            "fa-solid fa-truck-fast fa-fw",
            "fa-solid fa-pen-nib fa-fw",
            "fa-solid fa-arrow-up fa-fw",
            "fa-solid fa-hippo fa-fw",
            "fa-solid fa-face-smile fa-fw",
            "fa-regular fa-face-smile fa-fw",
            "fa-solid fa-calendar-days fa-fw",
            "fa-regular fa-calendar-days fa-fw",
            "fa-solid fa-paperclip fa-fw",
            "fa-solid fa-shield-halved fa-fw",
            "fa-solid fa-file fa-fw",
            "fa-regular fa-file fa-fw",
            "fa-solid fa-bell fa-fw",
            "fa-regular fa-bell fa-fw",
            "fa-solid fa-cart-shopping fa-fw",
            "fa-solid fa-clipboard fa-fw",
            "fa-regular fa-clipboard fa-fw",
            "fa-solid fa-filter fa-fw",
            "fa-solid fa-circle-info fa-fw",
            "fa-solid fa-arrow-up-from-bracket fa-fw",
            "fa-solid fa-bolt fa-fw",
            "fa-solid fa-car fa-fw",
            "fa-solid fa-ghost fa-fw",
            "fa-solid fa-mug-hot fa-fw",
            "fa-solid fa-circle-user fa-fw",
            "fa-regular fa-circle-user fa-fw",
            "fa-solid fa-pen fa-fw",
            "fa-solid fa-umbrella fa-fw",
            "fa-solid fa-gift fa-fw",
            "fa-solid fa-film fa-fw",
            "fa-solid fa-list fa-fw",
            "fa-solid fa-gear fa-fw",
            "fa-solid fa-trash fa-fw",
            "fa-solid fa-circle-up fa-fw",
            "fa-regular fa-circle-up fa-fw",
            "fa-solid fa-circle-down fa-fw",
            "fa-regular fa-circle-down fa-fw",
            "fa-solid fa-inbox fa-fw",
            "fa-solid fa-rotate-right fa-fw",
            "fa-solid fa-lock fa-fw",
            "fa-solid fa-headphones fa-fw",
            "fa-solid fa-barcode fa-fw",
            "fa-solid fa-tag fa-fw",
            "fa-solid fa-book fa-fw",
            "fa-solid fa-bookmark fa-fw",
            "fa-regular fa-bookmark fa-fw",
            "fa-solid fa-print fa-fw",
            "fa-solid fa-camera fa-fw",
            "fa-solid fa-font fa-fw",
            "fa-solid fa-video fa-fw",
            "fa-solid fa-circle-half-stroke fa-fw",
            "fa-solid fa-droplet fa-fw",
            "fa-solid fa-pen-to-square fa-fw",
            "fa-regular fa-pen-to-square fa-fw",
            "fa-solid fa-share-from-square fa-fw",
            "fa-regular fa-share-from-square fa-fw",
            "fa-solid fa-plus fa-fw",
            "fa-solid fa-minus fa-fw",
            "fa-solid fa-share fa-fw",
            "fa-solid fa-circle-exclamation fa-fw",
            "fa-solid fa-fire fa-fw",
            "fa-solid fa-eye fa-fw",
            "fa-regular fa-eye fa-fw",
            "fa-solid fa-eye-slash fa-fw",
            "fa-regular fa-eye-slash fa-fw",
            "fa-solid fa-plane fa-fw",
            "fa-solid fa-magnet fa-fw",
            "fa-solid fa-hand fa-fw",
            "fa-regular fa-hand fa-fw",
            "fa-solid fa-folder fa-fw",
            "fa-regular fa-folder fa-fw",
            "fa-solid fa-folder-open fa-fw",
            "fa-regular fa-folder-open fa-fw",
            "fa-solid fa-money-bill fa-fw",
            "fa-solid fa-thumbs-up fa-fw",
            "fa-regular fa-thumbs-up fa-fw",
            "fa-solid fa-thumbs-down fa-fw",
            "fa-regular fa-thumbs-down fa-fw",
            "fa-solid fa-comments fa-fw",
            "fa-regular fa-comments fa-fw",
            "fa-solid fa-lemon fa-fw",
            "fa-regular fa-lemon fa-fw",
            "fa-solid fa-key fa-fw",
            "fa-solid fa-thumbtack fa-fw",
            "fa-solid fa-gears fa-fw",
            "fa-solid fa-paper-plane fa-fw",
            "fa-regular fa-paper-plane fa-fw",
            "fa-solid fa-code fa-fw",
            "fa-solid fa-globe fa-fw",
            "fa-solid fa-truck fa-fw",
            "fa-solid fa-city fa-fw",
            "fa-solid fa-ticket fa-fw",
            "fa-solid fa-tree fa-fw",
            "fa-solid fa-wifi fa-fw",
            "fa-solid fa-paint-roller fa-fw",
            "fa-solid fa-bicycle fa-fw",
            "fa-solid fa-sliders fa-fw",
            "fa-solid fa-brush fa-fw",
            "fa-solid fa-hashtag fa-fw",
            "fa-solid fa-flask fa-fw",
            "fa-solid fa-briefcase fa-fw",
            "fa-solid fa-compass fa-fw",
            "fa-regular fa-compass fa-fw",
            "fa-solid fa-dumpster-fire fa-fw",
            "fa-solid fa-person fa-fw",
            "fa-solid fa-person-dress fa-fw",
            "fa-solid fa-address-book fa-fw",
            "fa-regular fa-address-book fa-fw",
            "fa-solid fa-bath fa-fw",
            "fa-solid fa-handshake fa-fw",
            "fa-regular fa-handshake fa-fw",
            "fa-solid fa-snowflake fa-fw",
            "fa-regular fa-snowflake fa-fw",
            "fa-solid fa-right-to-bracket fa-fw",
            "fa-solid fa-earth-americas fa-fw",
            "fa-solid fa-cloud-arrow-up fa-fw",
            "fa-solid fa-binoculars fa-fw",
            "fa-solid fa-palette fa-fw",
            "fa-solid fa-layer-group fa-fw",
            "fa-solid fa-users fa-fw",
            "fa-solid fa-gamepad fa-fw",
            "fa-solid fa-business-time fa-fw",
            "fa-solid fa-feather fa-fw",
            "fa-solid fa-sun fa-fw",
            "fa-regular fa-sun fa-fw",
            "fa-solid fa-link fa-fw",
            "fa-solid fa-pen-fancy fa-fw",
            "fa-solid fa-fish fa-fw",
            "fa-solid fa-bug fa-fw",
            "fa-solid fa-shop fa-fw",
            "fa-solid fa-mug-saucer fa-fw",
            "fa-solid fa-landmark fa-fw",
            "fa-solid fa-poo-storm fa-fw",
            "fa-solid fa-chart-simple fa-fw",
            "fa-solid fa-shirt fa-fw",
            "fa-solid fa-anchor fa-fw",
            "fa-solid fa-quote-left fa-fw",
            "fa-solid fa-bag-shopping fa-fw",
            "fa-solid fa-gauge fa-fw",
            "fa-solid fa-code-compare fa-fw",
            "fa-solid fa-user-secret fa-fw",
            "fa-solid fa-stethoscope fa-fw",
            "fa-solid fa-car-side fa-fw",
            "fa-solid fa-hand-holding-heart fa-fw",
            "fa-solid fa-truck-front fa-fw",
            "fa-solid fa-cable-car fa-fw",
            "fa-solid fa-mountain-sun fa-fw",
            "fa-solid fa-location-pin fa-fw",
            "fa-solid fa-info fa-fw",
            "fa-solid fa-user-minus fa-fw",
            "fa-solid fa-calendar fa-fw",
            "fa-regular fa-calendar fa-fw",
            "fa-solid fa-cart-plus fa-fw",
            "fa-solid fa-clock fa-fw",
            "fa-regular fa-clock fa-fw",
            "fa-solid fa-circle fa-fw",
            "fa-regular fa-circle fa-fw",
            "fa-solid fa-play fa-fw",
            "fa-solid fa-cross fa-fw",
            "fa-solid fa-backward fa-fw",
            "fa-solid fa-handshake-slash fa-fw",
            "fa-solid fa-chevron-up fa-fw",
            "fa-solid fa-passport fa-fw",
            "fa-solid fa-question fa-fw",
            "fa-solid fa-pencil fa-fw",
            "fa-solid fa-phone-volume fa-fw",
            "fa-solid fa-upload fa-fw",
            "fa-solid fa-strikethrough fa-fw",
            "fa-solid fa-credit-card fa-fw",
            "fa-regular fa-credit-card fa-fw",
            "fa-solid fa-street-view fa-fw",
            "fa-solid fa-database fa-fw",
            "fa-solid fa-copy fa-fw",
            "fa-regular fa-copy fa-fw",
            "fa-solid fa-mobile fa-fw",
            "fa-solid fa-square fa-fw",
            "fa-regular fa-square fa-fw",
            "fa-solid fa-sort fa-fw",
            "fa-solid fa-forward fa-fw",
            "fa-solid fa-hourglass-start fa-fw",
            "fa-solid fa-newspaper fa-fw",
            "fa-regular fa-newspaper fa-fw",
            "fa-solid fa-notes-medical fa-fw",
            "fa-solid fa-table fa-fw",
            "fa-solid fa-building fa-fw",
            "fa-regular fa-building fa-fw",
            "fa-solid fa-stop fa-fw",
            "fa-solid fa-store fa-fw",
            "fa-solid fa-flag fa-fw",
            "fa-regular fa-flag fa-fw",
            "fa-solid fa-file-excel fa-fw",
            "fa-regular fa-file-excel fa-fw",
            "fa-solid fa-network-wired fa-fw",
            "fa-solid fa-cash-register fa-fw",
            "fa-solid fa-file-export fa-fw",
            "fa-solid fa-hand-point-up fa-fw",
            "fa-regular fa-hand-point-up fa-fw",
            "fa-solid fa-angle-up fa-fw",
            "fa-solid fa-shield fa-fw",
            "fa-solid fa-address-card fa-fw",
            "fa-regular fa-address-card fa-fw",
            "fa-solid fa-expand fa-fw",
            "fa-solid fa-flag-checkered fa-fw",
            "fa-solid fa-quote-right fa-fw",
            "fa-solid fa-tags fa-fw",
            "fa-solid fa-server fa-fw",
            "fa-solid fa-user-nurse fa-fw",
            "fa-solid fa-video-slash fa-fw",
            "fa-solid fa-arrow-down fa-fw",
            "fa-solid fa-blog fa-fw",
            "fa-solid fa-school fa-fw",
            "fa-solid fa-file-invoice fa-fw",
            "fa-solid fa-rocket fa-fw",
            "fa-solid fa-spinner fa-fw",
            "fa-solid fa-tty fa-fw",
            "fa-solid fa-exclamation fa-fw",
            "fa-solid fa-water fa-fw",
            "fa-solid fa-registered fa-fw",
            "fa-regular fa-registered fa-fw",
            "fa-solid fa-signature fa-fw",
            "fa-solid fa-laptop fa-fw",
            "fa-solid fa-restroom fa-fw",
            "fa-solid fa-power-off fa-fw",
            "fa-solid fa-sitemap fa-fw",
            "fa-solid fa-icons fa-fw",
            "fa-solid fa-desktop fa-fw",
            "fa-solid fa-moon fa-fw",
            "fa-regular fa-moon fa-fw",
            "fa-solid fa-calendar-week fa-fw",
            "fa-solid fa-pause fa-fw",
            "fa-solid fa-file-word fa-fw",
            "fa-regular fa-file-word fa-fw",
            "fa-solid fa-vials fa-fw",
            "fa-solid fa-language fa-fw",
            "fa-solid fa-door-open fa-fw",
            "fa-solid fa-brain fa-fw",
            "fa-solid fa-hotel fa-fw",
            "fa-solid fa-marker fa-fw",
            "fa-solid fa-star-of-life fa-fw",
            "fa-solid fa-leaf fa-fw",
            "fa-solid fa-walkie-talkie fa-fw",
            "fa-solid fa-shower fa-fw",
            "fa-solid fa-caret-down fa-fw",
            "fa-solid fa-file-import fa-fw",
            "fa-solid fa-place-of-worship fa-fw",
            "fa-solid fa-wallet fa-fw",
            "fa-solid fa-slash fa-fw",
            "fa-solid fa-award fa-fw",
            "fa-solid fa-toggle-on fa-fw",
            "fa-solid fa-ship fa-fw",
            "fa-solid fa-chalkboard fa-fw",
            "fa-solid fa-hands fa-fw",
            "fa-solid fa-signal fa-fw",
            "fa-solid fa-motorcycle fa-fw",
            "fa-solid fa-arrow-up-right-from-square fa-fw",
            "fa-solid fa-audio-description fa-fw",
            "fa-solid fa-seedling fa-fw",
            "fa-solid fa-closed-captioning fa-fw",
            "fa-regular fa-closed-captioning fa-fw",
            "fa-solid fa-train fa-fw",
            "fa-solid fa-arrow-left fa-fw",
            "fa-solid fa-wrench fa-fw",
            "fa-solid fa-microchip fa-fw",
            "fa-solid fa-record-vinyl fa-fw",
            "fa-solid fa-trophy fa-fw",
            "fa-solid fa-hammer fa-fw",
            "fa-solid fa-diamond fa-fw",
            "fa-solid fa-robot fa-fw",
            "fa-solid fa-file-pdf fa-fw",
            "fa-regular fa-file-pdf fa-fw",
            "fa-solid fa-hospital fa-fw",
            "fa-regular fa-hospital fa-fw",
            "fa-solid fa-file-contract fa-fw",
            "fa-solid fa-square-xmark fa-fw",
            "fa-solid fa-square-check fa-fw",
            "fa-regular fa-square-check fa-fw",
            "fa-solid fa-crown fa-fw",
            "fa-solid fa-user-plus fa-fw",
            "fa-solid fa-virus fa-fw",
            "fa-solid fa-child fa-fw",
            "fa-solid fa-repeat fa-fw",
            "fa-solid fa-cube fa-fw",
            "fa-solid fa-copyright fa-fw",
            "fa-regular fa-copyright fa-fw",
            "fa-solid fa-medal fa-fw",
            "fa-solid fa-bullseye fa-fw",
            "fa-solid fa-mask fa-fw",
            "fa-solid fa-circle-check fa-fw",
            "fa-regular fa-circle-check fa-fw",
            "fa-solid fa-radio fa-fw",
            "fa-solid fa-reply fa-fw",
            "fa-solid fa-chair fa-fw",
            "fa-solid fa-route fa-fw",
            "fa-solid fa-plug fa-fw",
            "fa-solid fa-calculator fa-fw",
            "fa-solid fa-dragon fa-fw",
            "fa-solid fa-certificate fa-fw",
            "fa-solid fa-fingerprint fa-fw",
            "fa-solid fa-road fa-fw",
            "fa-solid fa-crosshairs fa-fw",
            "fa-solid fa-heading fa-fw",
            "fa-solid fa-percent fa-fw",
            "fa-solid fa-user-tie fa-fw",
            "fa-solid fa-square-minus fa-fw",
            "fa-regular fa-square-minus fa-fw",
            "fa-solid fa-i-cursor fa-fw",
            "fa-solid fa-church fa-fw",
            "fa-solid fa-joint fa-fw",
            "fa-solid fa-comments-dollar fa-fw",
            "fa-solid fa-truck-monster fa-fw",
            "fa-solid fa-recycle fa-fw",
            "fa-solid fa-warehouse fa-fw",
            "fa-solid fa-ruler fa-fw",
            "fa-solid fa-soap fa-fw",
            "fa-solid fa-scroll fa-fw",
            "fa-solid fa-coins fa-fw",
            "fa-solid fa-wind fa-fw",
            "fa-solid fa-baby fa-fw",
            "fa-solid fa-lightbulb fa-fw",
            "fa-regular fa-lightbulb fa-fw",
            "fa-solid fa-voicemail fa-fw",
            "fa-solid fa-puzzle-piece fa-fw",
            "fa-solid fa-keyboard fa-fw",
            "fa-regular fa-keyboard fa-fw",
            "fa-solid fa-clone fa-fw",
            "fa-regular fa-clone fa-fw",
            "fa-solid fa-eraser fa-fw",
            "fa-solid fa-wine-bottle fa-fw",
            "fa-solid fa-dice fa-fw",
            "fa-solid fa-receipt fa-fw",
            "fa-solid fa-ring fa-fw",
            "fa-solid fa-unlock fa-fw",
            "fa-solid fa-solar-panel fa-fw",
            "fa-solid fa-ruler-vertical fa-fw",
            "fa-solid fa-circle-notch fa-fw",
            "fa-solid fa-people-arrows fa-fw",
            "fa-solid fa-dollar-sign fa-fw",
            "fa-solid fa-tablet fa-fw",
            "fa-solid fa-not-equal fa-fw",
            "fa-solid fa-glasses fa-fw",
            "fa-solid fa-headset fa-fw",
            "fa-solid fa-code-branch fa-fw",
            "fa-solid fa-gopuram fa-fw",
            "fa-solid fa-images fa-fw",
            "fa-regular fa-images fa-fw",
            "fa-solid fa-window-restore fa-fw",
            "fa-regular fa-window-restore fa-fw",
            "fa-solid fa-industry fa-fw",
            "fa-solid fa-stamp fa-fw",
            "fa-solid fa-microphone-slash fa-fw",
            "fa-solid fa-cookie-bite fa-fw",
            "fa-solid fa-otter fa-fw",
            "fa-solid fa-chevron-down fa-fw",
            "fa-solid fa-kiwi-bird fa-fw",
            "fa-solid fa-viruses fa-fw",
            "fa-solid fa-umbrella-beach fa-fw",
            "fa-solid fa-subscript fa-fw",
            "fa-solid fa-tablets fa-fw",
            "fa-solid fa-microphone fa-fw",
            "fa-solid fa-border-none fa-fw",
            "fa-solid fa-dumbbell fa-fw",
            "fa-solid fa-plane-departure fa-fw",
            "fa-solid fa-z fa-fw",
            "fa-solid fa-yin-yang fa-fw",
            "fa-solid fa-yen-sign fa-fw",
            "fa-solid fa-y fa-fw",
            "fa-solid fa-xmarks-lines fa-fw",
            "fa-solid fa-x-ray fa-fw",
            "fa-solid fa-x fa-fw",
            "fa-solid fa-worm fa-fw",
            "fa-solid fa-won-sign fa-fw",
            "fa-solid fa-wine-glass-empty fa-fw",
            "fa-solid fa-wine-glass fa-fw",
            "fa-solid fa-window-minimize fa-fw",
            "fa-solid fa-window-maximize fa-fw",
            "fa-solid fa-whiskey-glass fa-fw",
            "fa-solid fa-wheelchair-move fa-fw",
            "fa-solid fa-wheelchair fa-fw",
            "fa-solid fa-wheat-awn-circle-exclamation fa-fw",
            "fa-solid fa-wheat-awn fa-fw",
            "fa-solid fa-weight-scale fa-fw",
            "fa-solid fa-weight-hanging fa-fw",
            "fa-solid fa-wave-square fa-fw",
            "fa-solid fa-water-ladder fa-fw",
            "fa-solid fa-wand-sparkles fa-fw",
            "fa-solid fa-wand-magic fa-fw",
            "fa-solid fa-w fa-fw",
            "fa-solid fa-vr-cardboard fa-fw",
            "fa-solid fa-volume-xmark fa-fw",
            "fa-solid fa-volume-off fa-fw",
            "fa-solid fa-volume-low fa-fw",
            "fa-solid fa-volume-high fa-fw",
            "fa-solid fa-volleyball fa-fw",
            "fa-solid fa-volcano fa-fw",
            "fa-solid fa-virus-slash fa-fw",
            "fa-solid fa-virus-covid-slash fa-fw",
            "fa-solid fa-virus-covid fa-fw",
            "fa-solid fa-vihara fa-fw",
            "fa-solid fa-vial-virus fa-fw",
            "fa-solid fa-vial-circle-check fa-fw",
            "fa-solid fa-vial fa-fw",
            "fa-solid fa-vest-patches fa-fw",
            "fa-solid fa-vest fa-fw",
            "fa-solid fa-venus-mars fa-fw",
            "fa-solid fa-venus-double fa-fw",
            "fa-solid fa-venus fa-fw",
            "fa-solid fa-vector-square fa-fw",
            "fa-solid fa-vault fa-fw",
            "fa-solid fa-van-shuttle fa-fw",
            "fa-solid fa-v fa-fw",
            "fa-solid fa-utensils fa-fw",
            "fa-solid fa-users-viewfinder fa-fw",
            "fa-solid fa-users-slash fa-fw",
            "fa-solid fa-users-rectangle fa-fw",
            "fa-solid fa-users-rays fa-fw",
            "fa-solid fa-users-line fa-fw",
            "fa-solid fa-users-gear fa-fw",
            "fa-solid fa-users-between-lines fa-fw",
            "fa-solid fa-user-xmark fa-fw",
            "fa-solid fa-user-tag fa-fw",
            "fa-solid fa-user-slash fa-fw",
            "fa-solid fa-user-shield fa-fw",
            "fa-solid fa-user-pen fa-fw",
            "fa-solid fa-user-ninja fa-fw",
            "fa-solid fa-user-lock fa-fw",
            "fa-solid fa-user-large-slash fa-fw",
            "fa-solid fa-user-large fa-fw",
            "fa-solid fa-user-injured fa-fw",
            "fa-solid fa-user-group fa-fw",
            "fa-solid fa-user-graduate fa-fw",
            "fa-solid fa-user-gear fa-fw",
            "fa-solid fa-user-doctor fa-fw",
            "fa-solid fa-user-clock fa-fw",
            "fa-solid fa-user-check fa-fw",
            "fa-solid fa-user-astronaut fa-fw",
            "fa-solid fa-up-right-from-square fa-fw",
            "fa-solid fa-up-right-and-down-left-from-center fa-fw",
            "fa-solid fa-up-long fa-fw",
            "fa-solid fa-up-down-left-right fa-fw",
            "fa-solid fa-up-down fa-fw",
            "fa-solid fa-unlock-keyhole fa-fw",
            "fa-solid fa-universal-access fa-fw",
            "fa-solid fa-underline fa-fw",
            "fa-solid fa-u fa-fw",
            "fa-solid fa-tv fa-fw",
            "fa-solid fa-turn-up fa-fw",
            "fa-solid fa-turn-down fa-fw",
            "fa-solid fa-turkish-lira-sign fa-fw",
            "fa-solid fa-truck-ramp-box fa-fw",
            "fa-solid fa-truck-plane fa-fw",
            "fa-solid fa-truck-pickup fa-fw",
            "fa-solid fa-truck-moving fa-fw",
            "fa-solid fa-truck-medical fa-fw",
            "fa-solid fa-truck-field-un fa-fw",
            "fa-solid fa-truck-field fa-fw",
            "fa-solid fa-truck-droplet fa-fw",
            "fa-solid fa-truck-arrow-right fa-fw",
            "fa-solid fa-trowel-bricks fa-fw",
            "fa-solid fa-trowel fa-fw",
            "fa-solid fa-triangle-exclamation fa-fw",
            "fa-solid fa-tree-city fa-fw",
            "fa-solid fa-trash-can-arrow-up fa-fw",
            "fa-solid fa-trash-can fa-fw",
            "fa-solid fa-trash-arrow-up fa-fw",
            "fa-solid fa-transgender fa-fw",
            "fa-solid fa-train-tram fa-fw",
            "fa-solid fa-train-subway fa-fw",
            "fa-solid fa-trailer fa-fw",
            "fa-solid fa-traffic-light fa-fw",
            "fa-solid fa-trademark fa-fw",
            "fa-solid fa-tractor fa-fw",
            "fa-solid fa-tower-observation fa-fw",
            "fa-solid fa-tower-cell fa-fw",
            "fa-solid fa-tower-broadcast fa-fw",
            "fa-solid fa-tornado fa-fw",
            "fa-solid fa-torii-gate fa-fw",
            "fa-solid fa-tooth fa-fw",
            "fa-solid fa-toolbox fa-fw",
            "fa-solid fa-toilets-portable fa-fw",
            "fa-solid fa-toilet-portable fa-fw",
            "fa-solid fa-toilet-paper-slash fa-fw",
            "fa-solid fa-toilet-paper fa-fw",
            "fa-solid fa-toilet fa-fw",
            "fa-solid fa-toggle-off fa-fw",
            "fa-solid fa-timeline fa-fw",
            "fa-solid fa-ticket-simple fa-fw",
            "fa-solid fa-thermometer fa-fw",
            "fa-solid fa-text-width fa-fw",
            "fa-solid fa-text-slash fa-fw",
            "fa-solid fa-text-height fa-fw",
            "fa-solid fa-terminal fa-fw",
            "fa-solid fa-tents fa-fw",
            "fa-solid fa-tent-arrows-down fa-fw",
            "fa-solid fa-tent-arrow-turn-left fa-fw",
            "fa-solid fa-tent-arrow-left-right fa-fw",
            "fa-solid fa-tent-arrow-down-to-line fa-fw",
            "fa-solid fa-tent fa-fw",
            "fa-solid fa-tenge-sign fa-fw",
            "fa-solid fa-temperature-three-quarters fa-fw",
            "fa-solid fa-temperature-quarter fa-fw",
            "fa-solid fa-temperature-low fa-fw",
            "fa-solid fa-temperature-high fa-fw",
            "fa-solid fa-temperature-half fa-fw",
            "fa-solid fa-temperature-full fa-fw",
            "fa-solid fa-temperature-empty fa-fw",
            "fa-solid fa-temperature-arrow-up fa-fw",
            "fa-solid fa-temperature-arrow-down fa-fw",
            "fa-solid fa-teeth-open fa-fw",
            "fa-solid fa-teeth fa-fw",
            "fa-solid fa-taxi fa-fw",
            "fa-solid fa-tarp-droplet fa-fw",
            "fa-solid fa-tarp fa-fw",
            "fa-solid fa-tape fa-fw",
            "fa-solid fa-tachograph-digital fa-fw",
            "fa-solid fa-tablet-screen-button fa-fw",
            "fa-solid fa-tablet-button fa-fw",
            "fa-solid fa-table-tennis-paddle-ball fa-fw",
            "fa-solid fa-table-list fa-fw",
            "fa-solid fa-table-columns fa-fw",
            "fa-solid fa-table-cells-large fa-fw",
            "fa-solid fa-table-cells fa-fw",
            "fa-solid fa-t fa-fw",
            "fa-solid fa-syringe fa-fw",
            "fa-solid fa-synagogue fa-fw",
            "fa-solid fa-swatchbook fa-fw",
            "fa-solid fa-superscript fa-fw",
            "fa-solid fa-sun-plant-wilt fa-fw",
            "fa-solid fa-suitcase-rolling fa-fw",
            "fa-solid fa-suitcase-medical fa-fw",
            "fa-solid fa-suitcase fa-fw",
            "fa-solid fa-stroopwafel fa-fw",
            "fa-solid fa-store-slash fa-fw",
            "fa-solid fa-stopwatch-20 fa-fw",
            "fa-solid fa-stopwatch fa-fw",
            "fa-solid fa-sterling-sign fa-fw",
            "fa-solid fa-star-of-david fa-fw",
            "fa-solid fa-star-half-stroke fa-fw",
            "fa-solid fa-star-half fa-fw",
            "fa-solid fa-star-and-crescent fa-fw",
            "fa-solid fa-stapler fa-fw",
            "fa-solid fa-stairs fa-fw",
            "fa-solid fa-staff-snake fa-fw",
            "fa-solid fa-square-virus fa-fw",
            "fa-solid fa-square-up-right fa-fw",
            "fa-solid fa-square-share-nodes fa-fw",
            "fa-solid fa-square-rss fa-fw",
            "fa-solid fa-square-root-variable fa-fw",
            "fa-solid fa-square-poll-vertical fa-fw",
            "fa-solid fa-square-poll-horizontal fa-fw",
            "fa-solid fa-square-plus fa-fw",
            "fa-solid fa-square-phone-flip fa-fw",
            "fa-solid fa-square-phone fa-fw",
            "fa-solid fa-square-person-confined fa-fw",
            "fa-solid fa-square-pen fa-fw",
            "fa-solid fa-square-parking fa-fw",
            "fa-solid fa-square-nfi fa-fw",
            "fa-solid fa-square-h fa-fw",
            "fa-solid fa-square-full fa-fw",
            "fa-solid fa-square-envelope fa-fw",
            "fa-solid fa-square-caret-up fa-fw",
            "fa-solid fa-square-caret-right fa-fw",
            "fa-solid fa-square-caret-left fa-fw",
            "fa-solid fa-square-caret-down fa-fw",
            "fa-solid fa-square-arrow-up-right fa-fw",
            "fa-solid fa-spray-can-sparkles fa-fw",
            "fa-solid fa-spray-can fa-fw",
            "fa-solid fa-spoon fa-fw",
            "fa-solid fa-splotch fa-fw",
            "fa-solid fa-spider fa-fw",
            "fa-solid fa-spell-check fa-fw",
            "fa-solid fa-spaghetti-monster-flying fa-fw",
            "fa-solid fa-spa fa-fw",
            "fa-solid fa-sort-up fa-fw",
            "fa-solid fa-sort-down fa-fw",
            "fa-solid fa-socks fa-fw",
            "fa-solid fa-snowplow fa-fw",
            "fa-solid fa-snowman fa-fw",
            "fa-solid fa-smoking fa-fw",
            "fa-solid fa-smog fa-fw",
            "fa-solid fa-sleigh fa-fw",
            "fa-solid fa-skull-crossbones fa-fw",
            "fa-solid fa-skull fa-fw",
            "fa-solid fa-sink fa-fw",
            "fa-solid fa-sim-card fa-fw",
            "fa-solid fa-signs-post fa-fw",
            "fa-solid fa-sign-hanging fa-fw",
            "fa-solid fa-shuttle-space fa-fw",
            "fa-solid fa-shuffle fa-fw",
            "fa-solid fa-shrimp fa-fw",
            "fa-solid fa-shop-slash fa-fw",
            "fa-solid fa-shop-lock fa-fw",
            "fa-solid fa-shoe-prints fa-fw",
            "fa-solid fa-shield-virus fa-fw",
            "fa-solid fa-shield-heart fa-fw",
            "fa-solid fa-shield-dog fa-fw",
            "fa-solid fa-shield-cat fa-fw",
            "fa-solid fa-shekel-sign fa-fw",
            "fa-solid fa-sheet-plastic fa-fw",
            "fa-solid fa-share-nodes fa-fw",
            "fa-solid fa-shapes fa-fw",
            "fa-solid fa-section fa-fw",
            "fa-solid fa-sd-card fa-fw",
            "fa-solid fa-scroll-torah fa-fw",
            "fa-solid fa-screwdriver-wrench fa-fw",
            "fa-solid fa-screwdriver fa-fw",
            "fa-solid fa-scissors fa-fw",
            "fa-solid fa-school-lock fa-fw",
            "fa-solid fa-school-flag fa-fw",
            "fa-solid fa-school-circle-xmark fa-fw",
            "fa-solid fa-school-circle-exclamation fa-fw",
            "fa-solid fa-school-circle-check fa-fw",
            "fa-solid fa-scale-unbalanced-flip fa-fw",
            "fa-solid fa-scale-unbalanced fa-fw",
            "fa-solid fa-scale-balanced fa-fw",
            "fa-solid fa-satellite-dish fa-fw",
            "fa-solid fa-satellite fa-fw",
            "fa-solid fa-sailboat fa-fw",
            "fa-solid fa-sack-xmark fa-fw",
            "fa-solid fa-sack-dollar fa-fw",
            "fa-solid fa-s fa-fw",
            "fa-solid fa-rupiah-sign fa-fw",
            "fa-solid fa-rupee-sign fa-fw",
            "fa-solid fa-ruler-horizontal fa-fw",
            "fa-solid fa-ruler-combined fa-fw",
            "fa-solid fa-rug fa-fw",
            "fa-solid fa-ruble-sign fa-fw",
            "fa-solid fa-rss fa-fw",
            "fa-solid fa-rotate-left fa-fw",
            "fa-solid fa-rotate fa-fw",
            "fa-solid fa-road-spikes fa-fw",
            "fa-solid fa-road-lock fa-fw",
            "fa-solid fa-road-circle-xmark fa-fw",
            "fa-solid fa-road-circle-exclamation fa-fw",
            "fa-solid fa-road-circle-check fa-fw",
            "fa-solid fa-road-bridge fa-fw",
            "fa-solid fa-road-barrier fa-fw",
            "fa-solid fa-right-long fa-fw",
            "fa-solid fa-right-left fa-fw",
            "fa-solid fa-right-from-bracket fa-fw",
            "fa-solid fa-ribbon fa-fw",
            "fa-solid fa-retweet fa-fw",
            "fa-solid fa-republican fa-fw",
            "fa-solid fa-reply-all fa-fw",
            "fa-solid fa-rectangle-xmark fa-fw",
            "fa-solid fa-rectangle-list fa-fw",
            "fa-solid fa-rectangle-ad fa-fw",
            "fa-solid fa-ranking-star fa-fw",
            "fa-solid fa-rainbow fa-fw",
            "fa-solid fa-radiation fa-fw",
            "fa-solid fa-r fa-fw",
            "fa-solid fa-qrcode fa-fw",
            "fa-solid fa-q fa-fw",
            "fa-solid fa-pump-soap fa-fw",
            "fa-solid fa-pump-medical fa-fw",
            "fa-solid fa-prescription-bottle-medical fa-fw",
            "fa-solid fa-prescription-bottle fa-fw",
            "fa-solid fa-prescription fa-fw",
            "fa-solid fa-poop fa-fw",
            "fa-solid fa-podcast fa-fw",
            "fa-solid fa-plus-minus fa-fw",
            "fa-solid fa-plug-circle-xmark fa-fw",
            "fa-solid fa-plug-circle-plus fa-fw",
            "fa-solid fa-plug-circle-minus fa-fw",
            "fa-solid fa-plug-circle-exclamation fa-fw",
            "fa-solid fa-plug-circle-check fa-fw",
            "fa-solid fa-plug-circle-bolt fa-fw",
            "fa-solid fa-plate-wheat fa-fw",
            "fa-solid fa-plant-wilt fa-fw",
            "fa-solid fa-plane-up fa-fw",
            "fa-solid fa-plane-slash fa-fw",
            "fa-solid fa-plane-lock fa-fw",
            "fa-solid fa-plane-circle-xmark fa-fw",
            "fa-solid fa-plane-circle-exclamation fa-fw",
            "fa-solid fa-plane-circle-check fa-fw",
            "fa-solid fa-plane-arrival fa-fw",
            "fa-solid fa-pizza-slice fa-fw",
            "fa-solid fa-pills fa-fw",
            "fa-solid fa-piggy-bank fa-fw",
            "fa-solid fa-photo-film fa-fw",
            "fa-solid fa-phone-slash fa-fw",
            "fa-solid fa-phone-flip fa-fw",
            "fa-solid fa-peso-sign fa-fw",
            "fa-solid fa-peseta-sign fa-fw",
            "fa-solid fa-person-walking-with-cane fa-fw",
            "fa-solid fa-person-walking-luggage fa-fw",
            "fa-solid fa-person-walking-dashed-line-arrow-right fa-fw",
            "fa-solid fa-person-walking-arrow-right fa-fw",
            "fa-solid fa-person-walking-arrow-loop-left fa-fw",
            "fa-solid fa-person-walking fa-fw",
            "fa-solid fa-person-through-window fa-fw",
            "fa-solid fa-person-swimming fa-fw",
            "fa-solid fa-person-snowboarding fa-fw",
            "fa-solid fa-person-skiing-nordic fa-fw",
            "fa-solid fa-person-skiing fa-fw",
            "fa-solid fa-person-skating fa-fw",
            "fa-solid fa-person-shelter fa-fw",
            "fa-solid fa-person-running fa-fw",
            "fa-solid fa-person-rifle fa-fw",
            "fa-solid fa-person-rays fa-fw",
            "fa-solid fa-person-pregnant fa-fw",
            "fa-solid fa-person-praying fa-fw",
            "fa-solid fa-person-military-to-person fa-fw",
            "fa-solid fa-person-military-rifle fa-fw",
            "fa-solid fa-person-military-pointing fa-fw",
            "fa-solid fa-person-hiking fa-fw",
            "fa-solid fa-person-harassing fa-fw",
            "fa-solid fa-person-half-dress fa-fw",
            "fa-solid fa-person-falling-burst fa-fw",
            "fa-solid fa-person-falling fa-fw",
            "fa-solid fa-person-drowning fa-fw",
            "fa-solid fa-person-dress-burst fa-fw",
            "fa-solid fa-person-dots-from-line fa-fw",
            "fa-solid fa-person-digging fa-fw",
            "fa-solid fa-person-circle-xmark fa-fw",
            "fa-solid fa-person-circle-question fa-fw",
            "fa-solid fa-person-circle-plus fa-fw",
            "fa-solid fa-person-circle-minus fa-fw",
            "fa-solid fa-person-circle-exclamation fa-fw",
            "fa-solid fa-person-circle-check fa-fw",
            "fa-solid fa-person-chalkboard fa-fw",
            "fa-solid fa-person-cane fa-fw",
            "fa-solid fa-person-burst fa-fw",
            "fa-solid fa-person-breastfeeding fa-fw",
            "fa-solid fa-person-booth fa-fw",
            "fa-solid fa-person-biking fa-fw",
            "fa-solid fa-person-arrow-up-from-line fa-fw",
            "fa-solid fa-person-arrow-down-to-line fa-fw",
            "fa-solid fa-pepper-hot fa-fw",
            "fa-solid fa-people-roof fa-fw",
            "fa-solid fa-people-robbery fa-fw",
            "fa-solid fa-people-pulling fa-fw",
            "fa-solid fa-people-line fa-fw",
            "fa-solid fa-people-group fa-fw",
            "fa-solid fa-people-carry-box fa-fw",
            "fa-solid fa-pen-ruler fa-fw",
            "fa-solid fa-pen-clip fa-fw",
            "fa-solid fa-peace fa-fw",
            "fa-solid fa-paw fa-fw",
            "fa-solid fa-paste fa-fw",
            "fa-solid fa-paragraph fa-fw",
            "fa-solid fa-parachute-box fa-fw",
            "fa-solid fa-panorama fa-fw",
            "fa-solid fa-pallet fa-fw",
            "fa-solid fa-paintbrush fa-fw",
            "fa-solid fa-pager fa-fw",
            "fa-solid fa-p fa-fw",
            "fa-solid fa-outdent fa-fw",
            "fa-solid fa-om fa-fw",
            "fa-solid fa-oil-well fa-fw",
            "fa-solid fa-oil-can fa-fw",
            "fa-solid fa-object-ungroup fa-fw",
            "fa-solid fa-object-group fa-fw",
            "fa-solid fa-o fa-fw",
            "fa-solid fa-note-sticky fa-fw",
            "fa-solid fa-notdef fa-fw",
            "fa-solid fa-neuter fa-fw",
            "fa-solid fa-naira-sign fa-fw",
            "fa-solid fa-n fa-fw",
            "fa-solid fa-mountain-city fa-fw",
            "fa-solid fa-mountain fa-fw",
            "fa-solid fa-mound fa-fw",
            "fa-solid fa-mosquito-net fa-fw",
            "fa-solid fa-mosquito fa-fw",
            "fa-solid fa-mosque fa-fw",
            "fa-solid fa-mortar-pestle fa-fw",
            "fa-solid fa-monument fa-fw",
            "fa-solid fa-money-check-dollar fa-fw",
            "fa-solid fa-money-check fa-fw",
            "fa-solid fa-money-bills fa-fw",
            "fa-solid fa-money-bill-wheat fa-fw",
            "fa-solid fa-money-bill-wave fa-fw",
            "fa-solid fa-money-bill-trend-up fa-fw",
            "fa-solid fa-money-bill-transfer fa-fw",
            "fa-solid fa-money-bill-1-wave fa-fw",
            "fa-solid fa-money-bill-1 fa-fw",
            "fa-solid fa-mobile-screen-button fa-fw",
            "fa-solid fa-mobile-screen fa-fw",
            "fa-solid fa-mobile-retro fa-fw",
            "fa-solid fa-mobile-button fa-fw",
            "fa-solid fa-mitten fa-fw",
            "fa-solid fa-minimize fa-fw",
            "fa-solid fa-mill-sign fa-fw",
            "fa-solid fa-microscope fa-fw",
            "fa-solid fa-microphone-lines-slash fa-fw",
            "fa-solid fa-microphone-lines fa-fw",
            "fa-solid fa-meteor fa-fw",
            "fa-solid fa-message fa-fw",
            "fa-solid fa-mercury fa-fw",
            "fa-solid fa-menorah fa-fw",
            "fa-solid fa-memory fa-fw",
            "fa-solid fa-maximize fa-fw",
            "fa-solid fa-mattress-pillow fa-fw",
            "fa-solid fa-masks-theater fa-fw",
            "fa-solid fa-mask-ventilator fa-fw",
            "fa-solid fa-mask-face fa-fw",
            "fa-solid fa-martini-glass-empty fa-fw",
            "fa-solid fa-martini-glass-citrus fa-fw",
            "fa-solid fa-martini-glass fa-fw",
            "fa-solid fa-mars-stroke-up fa-fw",
            "fa-solid fa-mars-stroke-right fa-fw",
            "fa-solid fa-mars-stroke fa-fw",
            "fa-solid fa-mars-double fa-fw",
            "fa-solid fa-mars-and-venus-burst fa-fw",
            "fa-solid fa-mars-and-venus fa-fw",
            "fa-solid fa-mars fa-fw",
            "fa-solid fa-map-pin fa-fw",
            "fa-solid fa-map-location-dot fa-fw",
            "fa-solid fa-map-location fa-fw",
            "fa-solid fa-map fa-fw",
            "fa-solid fa-manat-sign fa-fw",
            "fa-solid fa-magnifying-glass-plus fa-fw",
            "fa-solid fa-magnifying-glass-minus fa-fw",
            "fa-solid fa-magnifying-glass-location fa-fw",
            "fa-solid fa-magnifying-glass-dollar fa-fw",
            "fa-solid fa-magnifying-glass-chart fa-fw",
            "fa-solid fa-magnifying-glass-arrow-right fa-fw",
            "fa-solid fa-m fa-fw",
            "fa-solid fa-lungs-virus fa-fw",
            "fa-solid fa-lungs fa-fw",
            "fa-solid fa-locust fa-fw",
            "fa-solid fa-lock-open fa-fw",
            "fa-solid fa-location-pin-lock fa-fw",
            "fa-solid fa-location-crosshairs fa-fw",
            "fa-solid fa-location-arrow fa-fw",
            "fa-solid fa-litecoin-sign fa-fw",
            "fa-solid fa-list-ul fa-fw",
            "fa-solid fa-list-ol fa-fw",
            "fa-solid fa-list-check fa-fw",
            "fa-solid fa-lira-sign fa-fw",
            "fa-solid fa-link-slash fa-fw",
            "fa-solid fa-lines-leaning fa-fw",
            "fa-solid fa-life-ring fa-fw",
            "fa-solid fa-less-than-equal fa-fw",
            "fa-solid fa-less-than fa-fw",
            "fa-solid fa-left-right fa-fw",
            "fa-solid fa-left-long fa-fw",
            "fa-solid fa-lari-sign fa-fw",
            "fa-solid fa-laptop-medical fa-fw",
            "fa-solid fa-laptop-file fa-fw",
            "fa-solid fa-laptop-code fa-fw",
            "fa-solid fa-landmark-flag fa-fw",
            "fa-solid fa-landmark-dome fa-fw",
            "fa-solid fa-land-mine-on fa-fw",
            "fa-solid fa-l fa-fw",
            "fa-solid fa-kitchen-set fa-fw",
            "fa-solid fa-kit-medical fa-fw",
            "fa-solid fa-kip-sign fa-fw",
            "fa-solid fa-khanda fa-fw",
            "fa-solid fa-kaaba fa-fw",
            "fa-solid fa-k fa-fw",
            "fa-solid fa-jug-detergent fa-fw",
            "fa-solid fa-jet-fighter-up fa-fw",
            "fa-solid fa-jet-fighter fa-fw",
            "fa-solid fa-jedi fa-fw",
            "fa-solid fa-jar-wheat fa-fw",
            "fa-solid fa-jar fa-fw",
            "fa-solid fa-j fa-fw",
            "fa-solid fa-italic fa-fw",
            "fa-solid fa-infinity fa-fw",
            "fa-solid fa-indian-rupee-sign fa-fw",
            "fa-solid fa-indent fa-fw",
            "fa-solid fa-image-portrait fa-fw",
            "fa-solid fa-igloo fa-fw",
            "fa-solid fa-id-card-clip fa-fw",
            "fa-solid fa-id-card fa-fw",
            "fa-solid fa-id-badge fa-fw",
            "fa-solid fa-icicles fa-fw",
            "fa-solid fa-ice-cream fa-fw",
            "fa-solid fa-i fa-fw",
            "fa-solid fa-hurricane fa-fw",
            "fa-solid fa-hryvnia-sign fa-fw",
            "fa-solid fa-house-user fa-fw",
            "fa-solid fa-house-tsunami fa-fw",
            "fa-solid fa-house-signal fa-fw",
            "fa-solid fa-house-medical-flag fa-fw",
            "fa-solid fa-house-medical-circle-xmark fa-fw",
            "fa-solid fa-house-medical-circle-exclamation fa-fw",
            "fa-solid fa-house-medical-circle-check fa-fw",
            "fa-solid fa-house-medical fa-fw",
            "fa-solid fa-house-lock fa-fw",
            "fa-solid fa-house-laptop fa-fw",
            "fa-solid fa-house-flood-water-circle-arrow-right fa-fw",
            "fa-solid fa-house-flood-water fa-fw",
            "fa-solid fa-house-flag fa-fw",
            "fa-solid fa-house-fire fa-fw",
            "fa-solid fa-house-crack fa-fw",
            "fa-solid fa-house-circle-xmark fa-fw",
            "fa-solid fa-house-circle-exclamation fa-fw",
            "fa-solid fa-house-circle-check fa-fw",
            "fa-solid fa-house-chimney-window fa-fw",
            "fa-solid fa-house-chimney-user fa-fw",
            "fa-solid fa-house-chimney-medical fa-fw",
            "fa-solid fa-house-chimney-crack fa-fw",
            "fa-solid fa-house-chimney fa-fw",
            "fa-solid fa-hourglass-half fa-fw",
            "fa-solid fa-hourglass-end fa-fw",
            "fa-solid fa-hourglass fa-fw",
            "fa-solid fa-hotdog fa-fw",
            "fa-solid fa-hot-tub-person fa-fw",
            "fa-solid fa-hospital-user fa-fw",
            "fa-solid fa-horse-head fa-fw",
            "fa-solid fa-horse fa-fw",
            "fa-solid fa-holly-berry fa-fw",
            "fa-solid fa-hockey-puck fa-fw",
            "fa-solid fa-hill-rockslide fa-fw",
            "fa-solid fa-hill-avalanche fa-fw",
            "fa-solid fa-highlighter fa-fw",
            "fa-solid fa-helmet-un fa-fw",
            "fa-solid fa-helmet-safety fa-fw",
            "fa-solid fa-helicopter-symbol fa-fw",
            "fa-solid fa-helicopter fa-fw",
            "fa-solid fa-heart-pulse fa-fw",
            "fa-solid fa-heart-crack fa-fw",
            "fa-solid fa-heart-circle-xmark fa-fw",
            "fa-solid fa-heart-circle-plus fa-fw",
            "fa-solid fa-heart-circle-minus fa-fw",
            "fa-solid fa-heart-circle-exclamation fa-fw",
            "fa-solid fa-heart-circle-check fa-fw",
            "fa-solid fa-heart-circle-bolt fa-fw",
            "fa-solid fa-headphones-simple fa-fw",
            "fa-solid fa-head-side-virus fa-fw",
            "fa-solid fa-head-side-mask fa-fw",
            "fa-solid fa-head-side-cough-slash fa-fw",
            "fa-solid fa-head-side-cough fa-fw",
            "fa-solid fa-hat-wizard fa-fw",
            "fa-solid fa-hat-cowboy-side fa-fw",
            "fa-solid fa-hat-cowboy fa-fw",
            "fa-solid fa-hard-drive fa-fw",
            "fa-solid fa-hanukiah fa-fw",
            "fa-solid fa-handshake-simple-slash fa-fw",
            "fa-solid fa-handshake-simple fa-fw",
            "fa-solid fa-handshake-angle fa-fw",
            "fa-solid fa-hands-praying fa-fw",
            "fa-solid fa-hands-holding-circle fa-fw",
            "fa-solid fa-hands-holding-child fa-fw",
            "fa-solid fa-hands-holding fa-fw",
            "fa-solid fa-hands-clapping fa-fw",
            "fa-solid fa-hands-bubbles fa-fw",
            "fa-solid fa-hands-bound fa-fw",
            "fa-solid fa-hands-asl-interpreting fa-fw",
            "fa-solid fa-handcuffs fa-fw",
            "fa-solid fa-hand-spock fa-fw",
            "fa-solid fa-hand-sparkles fa-fw",
            "fa-solid fa-hand-scissors fa-fw",
            "fa-solid fa-hand-pointer fa-fw",
            "fa-solid fa-hand-point-right fa-fw",
            "fa-solid fa-hand-point-left fa-fw",
            "fa-solid fa-hand-point-down fa-fw",
            "fa-solid fa-hand-peace fa-fw",
            "fa-solid fa-hand-middle-finger fa-fw",
            "fa-solid fa-hand-lizard fa-fw",
            "fa-solid fa-hand-holding-medical fa-fw",
            "fa-solid fa-hand-holding-hand fa-fw",
            "fa-solid fa-hand-holding-droplet fa-fw",
            "fa-solid fa-hand-holding-dollar fa-fw",
            "fa-solid fa-hand-holding fa-fw",
            "fa-solid fa-hand-fist fa-fw",
            "fa-solid fa-hand-dots fa-fw",
            "fa-solid fa-hand-back-fist fa-fw",
            "fa-solid fa-hamsa fa-fw",
            "fa-solid fa-h fa-fw",
            "fa-solid fa-gun fa-fw",
            "fa-solid fa-guitar fa-fw",
            "fa-solid fa-guarani-sign fa-fw",
            "fa-solid fa-group-arrows-rotate fa-fw",
            "fa-solid fa-grip-vertical fa-fw",
            "fa-solid fa-grip-lines-vertical fa-fw",
            "fa-solid fa-grip-lines fa-fw",
            "fa-solid fa-grip fa-fw",
            "fa-solid fa-greater-than-equal fa-fw",
            "fa-solid fa-greater-than fa-fw",
            "fa-solid fa-graduation-cap fa-fw",
            "fa-solid fa-golf-ball-tee fa-fw",
            "fa-solid fa-glass-water-droplet fa-fw",
            "fa-solid fa-glass-water fa-fw",
            "fa-solid fa-gifts fa-fw",
            "fa-solid fa-genderless fa-fw",
            "fa-solid fa-gem fa-fw",
            "fa-solid fa-gavel fa-fw",
            "fa-solid fa-gauge-simple-high fa-fw",
            "fa-solid fa-gauge-simple fa-fw",
            "fa-solid fa-gauge-high fa-fw",
            "fa-solid fa-gas-pump fa-fw",
            "fa-solid fa-g fa-fw",
            "fa-solid fa-futbol fa-fw",
            "fa-solid fa-frog fa-fw",
            "fa-solid fa-franc-sign fa-fw",
            "fa-solid fa-forward-step fa-fw",
            "fa-solid fa-forward-fast fa-fw",
            "fa-solid fa-football fa-fw",
            "fa-solid fa-font-awesome fa-fw",
            "fa-solid fa-folder-tree fa-fw",
            "fa-solid fa-folder-plus fa-fw",
            "fa-solid fa-folder-minus fa-fw",
            "fa-solid fa-folder-closed fa-fw",
            "fa-solid fa-florin-sign fa-fw",
            "fa-solid fa-floppy-disk fa-fw",
            "fa-solid fa-flask-vial fa-fw",
            "fa-solid fa-flag-usa fa-fw",
            "fa-solid fa-fish-fins fa-fw",
            "fa-solid fa-fire-flame-simple fa-fw",
            "fa-solid fa-fire-flame-curved fa-fw",
            "fa-solid fa-fire-extinguisher fa-fw",
            "fa-solid fa-fire-burner fa-fw",
            "fa-solid fa-filter-circle-xmark fa-fw",
            "fa-solid fa-filter-circle-dollar fa-fw",
            "fa-solid fa-fill-drip fa-fw",
            "fa-solid fa-fill fa-fw",
            "fa-solid fa-file-zipper fa-fw",
            "fa-solid fa-file-waveform fa-fw",
            "fa-solid fa-file-video fa-fw",
            "fa-solid fa-file-signature fa-fw",
            "fa-solid fa-file-shield fa-fw",
            "fa-solid fa-file-prescription fa-fw",
            "fa-solid fa-file-powerpoint fa-fw",
            "fa-solid fa-file-pen fa-fw",
            "fa-solid fa-file-medical fa-fw",
            "fa-solid fa-file-lines fa-fw",
            "fa-solid fa-file-invoice-dollar fa-fw",
            "fa-solid fa-file-image fa-fw",
            "fa-solid fa-file-csv fa-fw",
            "fa-solid fa-file-code fa-fw",
            "fa-solid fa-file-circle-xmark fa-fw",
            "fa-solid fa-file-circle-question fa-fw",
            "fa-solid fa-file-circle-plus fa-fw",
            "fa-solid fa-file-circle-minus fa-fw",
            "fa-solid fa-file-circle-exclamation fa-fw",
            "fa-solid fa-file-circle-check fa-fw",
            "fa-solid fa-file-audio fa-fw",
            "fa-solid fa-file-arrow-up fa-fw",
            "fa-solid fa-file-arrow-down fa-fw",
            "fa-solid fa-ferry fa-fw",
            "fa-solid fa-feather-pointed fa-fw",
            "fa-solid fa-fax fa-fw",
            "fa-solid fa-faucet-drip fa-fw",
            "fa-solid fa-faucet fa-fw",
            "fa-solid fa-fan fa-fw",
            "fa-solid fa-face-tired fa-fw",
            "fa-solid fa-face-surprise fa-fw",
            "fa-solid fa-face-smile-wink fa-fw",
            "fa-solid fa-face-smile-beam fa-fw",
            "fa-solid fa-face-sad-tear fa-fw",
            "fa-solid fa-face-sad-cry fa-fw",
            "fa-solid fa-face-rolling-eyes fa-fw",
            "fa-solid fa-face-meh-blank fa-fw",
            "fa-solid fa-face-meh fa-fw",
            "fa-solid fa-face-laugh-wink fa-fw",
            "fa-solid fa-face-laugh-squint fa-fw",
            "fa-solid fa-face-laugh-beam fa-fw",
            "fa-solid fa-face-laugh fa-fw",
            "fa-solid fa-face-kiss-wink-heart fa-fw",
            "fa-solid fa-face-kiss-beam fa-fw",
            "fa-solid fa-face-kiss fa-fw",
            "fa-solid fa-face-grin-wink fa-fw",
            "fa-solid fa-face-grin-wide fa-fw",
            "fa-solid fa-face-grin-tongue-wink fa-fw",
            "fa-solid fa-face-grin-tongue-squint fa-fw",
            "fa-solid fa-face-grin-tongue fa-fw",
            "fa-solid fa-face-grin-tears fa-fw",
            "fa-solid fa-face-grin-stars fa-fw",
            "fa-solid fa-face-grin-squint-tears fa-fw",
            "fa-solid fa-face-grin-squint fa-fw",
            "fa-solid fa-face-grin-hearts fa-fw",
            "fa-solid fa-face-grin-beam-sweat fa-fw",
            "fa-solid fa-face-grin-beam fa-fw",
            "fa-solid fa-face-grin fa-fw",
            "fa-solid fa-face-grimace fa-fw",
            "fa-solid fa-face-frown-open fa-fw",
            "fa-solid fa-face-frown fa-fw",
            "fa-solid fa-face-flushed fa-fw",
            "fa-solid fa-face-dizzy fa-fw",
            "fa-solid fa-face-angry fa-fw",
            "fa-solid fa-f fa-fw",
            "fa-solid fa-eye-low-vision fa-fw",
            "fa-solid fa-eye-dropper fa-fw",
            "fa-solid fa-explosion fa-fw",
            "fa-solid fa-euro-sign fa-fw",
            "fa-solid fa-ethernet fa-fw",
            "fa-solid fa-equals fa-fw",
            "fa-solid fa-envelopes-bulk fa-fw",
            "fa-solid fa-envelope-open-text fa-fw",
            "fa-solid fa-envelope-open fa-fw",
            "fa-solid fa-envelope-circle-check fa-fw",
            "fa-solid fa-ellipsis-vertical fa-fw",
            "fa-solid fa-ellipsis fa-fw",
            "fa-solid fa-elevator fa-fw",
            "fa-solid fa-eject fa-fw",
            "fa-solid fa-egg fa-fw",
            "fa-solid fa-earth-oceania fa-fw",
            "fa-solid fa-earth-europe fa-fw",
            "fa-solid fa-earth-asia fa-fw",
            "fa-solid fa-earth-africa fa-fw",
            "fa-solid fa-ear-listen fa-fw",
            "fa-solid fa-ear-deaf fa-fw",
            "fa-solid fa-e fa-fw",
            "fa-solid fa-dungeon fa-fw",
            "fa-solid fa-dumpster fa-fw",
            "fa-solid fa-drumstick-bite fa-fw",
            "fa-solid fa-drum-steelpan fa-fw",
            "fa-solid fa-drum fa-fw",
            "fa-solid fa-droplet-slash fa-fw",
            "fa-solid fa-draw-polygon fa-fw",
            "fa-solid fa-down-long fa-fw",
            "fa-solid fa-down-left-and-up-right-to-center fa-fw",
            "fa-solid fa-dove fa-fw",
            "fa-solid fa-door-closed fa-fw",
            "fa-solid fa-dong-sign fa-fw",
            "fa-solid fa-dolly fa-fw",
            "fa-solid fa-dog fa-fw",
            "fa-solid fa-dna fa-fw",
            "fa-solid fa-divide fa-fw",
            "fa-solid fa-display fa-fw",
            "fa-solid fa-disease fa-fw",
            "fa-solid fa-dice-two fa-fw",
            "fa-solid fa-dice-three fa-fw",
            "fa-solid fa-dice-six fa-fw",
            "fa-solid fa-dice-one fa-fw",
            "fa-solid fa-dice-four fa-fw",
            "fa-solid fa-dice-five fa-fw",
            "fa-solid fa-dice-d6 fa-fw",
            "fa-solid fa-dice-d20 fa-fw",
            "fa-solid fa-diamond-turn-right fa-fw",
            "fa-solid fa-diagram-successor fa-fw",
            "fa-solid fa-diagram-project fa-fw",
            "fa-solid fa-diagram-predecessor fa-fw",
            "fa-solid fa-diagram-next fa-fw",
            "fa-solid fa-dharmachakra fa-fw",
            "fa-solid fa-democrat fa-fw",
            "fa-solid fa-delete-left fa-fw",
            "fa-solid fa-d fa-fw",
            "fa-solid fa-cubes-stacked fa-fw",
            "fa-solid fa-cubes fa-fw",
            "fa-solid fa-cruzeiro-sign fa-fw",
            "fa-solid fa-crutch fa-fw",
            "fa-solid fa-crow fa-fw",
            "fa-solid fa-crop-simple fa-fw",
            "fa-solid fa-crop fa-fw",
            "fa-solid fa-cow fa-fw",
            "fa-solid fa-couch fa-fw",
            "fa-solid fa-cookie fa-fw",
            "fa-solid fa-computer-mouse fa-fw",
            "fa-solid fa-computer fa-fw",
            "fa-solid fa-compress fa-fw",
            "fa-solid fa-compass-drafting fa-fw",
            "fa-solid fa-compact-disc fa-fw",
            "fa-solid fa-comment-sms fa-fw",
            "fa-solid fa-comment-slash fa-fw",
            "fa-solid fa-comment-medical fa-fw",
            "fa-solid fa-comment-dots fa-fw",
            "fa-solid fa-comment-dollar fa-fw",
            "fa-solid fa-colon-sign fa-fw",
            "fa-solid fa-code-pull-request fa-fw",
            "fa-solid fa-code-merge fa-fw",
            "fa-solid fa-code-fork fa-fw",
            "fa-solid fa-code-commit fa-fw",
            "fa-solid fa-clover fa-fw",
            "fa-solid fa-cloud-sun-rain fa-fw",
            "fa-solid fa-cloud-sun fa-fw",
            "fa-solid fa-cloud-showers-water fa-fw",
            "fa-solid fa-cloud-showers-heavy fa-fw",
            "fa-solid fa-cloud-rain fa-fw",
            "fa-solid fa-cloud-moon-rain fa-fw",
            "fa-solid fa-cloud-moon fa-fw",
            "fa-solid fa-cloud-meatball fa-fw",
            "fa-solid fa-cloud-bolt fa-fw",
            "fa-solid fa-cloud-arrow-down fa-fw",
            "fa-solid fa-clock-rotate-left fa-fw",
            "fa-solid fa-clipboard-user fa-fw",
            "fa-solid fa-clipboard-question fa-fw",
            "fa-solid fa-clipboard-list fa-fw",
            "fa-solid fa-clipboard-check fa-fw",
            "fa-solid fa-clapperboard fa-fw",
            "fa-solid fa-circle-stop fa-fw",
            "fa-solid fa-circle-right fa-fw",
            "fa-solid fa-circle-radiation fa-fw",
            "fa-solid fa-circle-question fa-fw",
            "fa-solid fa-circle-plus fa-fw",
            "fa-solid fa-circle-play fa-fw",
            "fa-solid fa-circle-pause fa-fw",
            "fa-solid fa-circle-nodes fa-fw",
            "fa-solid fa-circle-minus fa-fw",
            "fa-solid fa-circle-left fa-fw",
            "fa-solid fa-circle-h fa-fw",
            "fa-solid fa-circle-dot fa-fw",
            "fa-solid fa-circle-dollar-to-slot fa-fw",
            "fa-solid fa-circle-chevron-up fa-fw",
            "fa-solid fa-circle-chevron-right fa-fw",
            "fa-solid fa-circle-chevron-left fa-fw",
            "fa-solid fa-circle-chevron-down fa-fw",
            "fa-solid fa-circle-arrow-up fa-fw",
            "fa-solid fa-circle-arrow-right fa-fw",
            "fa-solid fa-circle-arrow-left fa-fw",
            "fa-solid fa-circle-arrow-down fa-fw",
            "fa-solid fa-children fa-fw",
            "fa-solid fa-child-reaching fa-fw",
            "fa-solid fa-child-dress fa-fw",
            "fa-solid fa-child-combatant fa-fw",
            "fa-solid fa-chevron-right fa-fw",
            "fa-solid fa-chevron-left fa-fw",
            "fa-solid fa-chess-rook fa-fw",
            "fa-solid fa-chess-queen fa-fw",
            "fa-solid fa-chess-pawn fa-fw",
            "fa-solid fa-chess-knight fa-fw",
            "fa-solid fa-chess-king fa-fw",
            "fa-solid fa-chess-board fa-fw",
            "fa-solid fa-chess-bishop fa-fw",
            "fa-solid fa-chess fa-fw",
            "fa-solid fa-cheese fa-fw",
            "fa-solid fa-check-to-slot fa-fw",
            "fa-solid fa-check-double fa-fw",
            "fa-solid fa-chart-pie fa-fw",
            "fa-solid fa-chart-line fa-fw",
            "fa-solid fa-chart-gantt fa-fw",
            "fa-solid fa-chart-column fa-fw",
            "fa-solid fa-chart-bar fa-fw",
            "fa-solid fa-chart-area fa-fw",
            "fa-solid fa-charging-station fa-fw",
            "fa-solid fa-champagne-glasses fa-fw",
            "fa-solid fa-chalkboard-user fa-fw",
            "fa-solid fa-cent-sign fa-fw",
            "fa-solid fa-cedi-sign fa-fw",
            "fa-solid fa-cat fa-fw",
            "fa-solid fa-cart-flatbed-suitcase fa-fw",
            "fa-solid fa-cart-flatbed fa-fw",
            "fa-solid fa-cart-arrow-down fa-fw",
            "fa-solid fa-carrot fa-fw",
            "fa-solid fa-caret-right fa-fw",
            "fa-solid fa-caret-left fa-fw",
            "fa-solid fa-caravan fa-fw",
            "fa-solid fa-car-tunnel fa-fw",
            "fa-solid fa-car-rear fa-fw",
            "fa-solid fa-car-on fa-fw",
            "fa-solid fa-car-burst fa-fw",
            "fa-solid fa-car-battery fa-fw",
            "fa-solid fa-capsules fa-fw",
            "fa-solid fa-cannabis fa-fw",
            "fa-solid fa-candy-cane fa-fw",
            "fa-solid fa-campground fa-fw",
            "fa-solid fa-camera-rotate fa-fw",
            "fa-solid fa-calendar-xmark fa-fw",
            "fa-solid fa-calendar-plus fa-fw",
            "fa-solid fa-calendar-minus fa-fw",
            "fa-solid fa-calendar-day fa-fw",
            "fa-solid fa-calendar-check fa-fw",
            "fa-solid fa-cake-candles fa-fw",
            "fa-solid fa-c fa-fw",
            "fa-solid fa-bus-simple fa-fw",
            "fa-solid fa-bus fa-fw",
            "fa-solid fa-burst fa-fw",
            "fa-solid fa-burger fa-fw",
            "fa-solid fa-bullhorn fa-fw",
            "fa-solid fa-building-wheat fa-fw",
            "fa-solid fa-building-user fa-fw",
            "fa-solid fa-building-un fa-fw",
            "fa-solid fa-building-shield fa-fw",
            "fa-solid fa-building-ngo fa-fw",
            "fa-solid fa-building-lock fa-fw",
            "fa-solid fa-building-flag fa-fw",
            "fa-solid fa-building-columns fa-fw",
            "fa-solid fa-building-circle-xmark fa-fw",
            "fa-solid fa-building-circle-exclamation fa-fw",
            "fa-solid fa-building-circle-check fa-fw",
            "fa-solid fa-building-circle-arrow-right fa-fw",
            "fa-solid fa-bugs fa-fw",
            "fa-solid fa-bug-slash fa-fw",
            "fa-solid fa-bucket fa-fw",
            "fa-solid fa-broom-ball fa-fw",
            "fa-solid fa-broom fa-fw",
            "fa-solid fa-briefcase-medical fa-fw",
            "fa-solid fa-bridge-water fa-fw",
            "fa-solid fa-bridge-lock fa-fw",
            "fa-solid fa-bridge-circle-xmark fa-fw",
            "fa-solid fa-bridge-circle-exclamation fa-fw",
            "fa-solid fa-bridge-circle-check fa-fw",
            "fa-solid fa-bridge fa-fw",
            "fa-solid fa-bread-slice fa-fw",
            "fa-solid fa-brazilian-real-sign fa-fw",
            "fa-solid fa-braille fa-fw",
            "fa-solid fa-boxes-stacked fa-fw",
            "fa-solid fa-boxes-packing fa-fw",
            "fa-solid fa-box-tissue fa-fw",
            "fa-solid fa-box-open fa-fw",
            "fa-solid fa-box-archive fa-fw",
            "fa-solid fa-box fa-fw",
            "fa-solid fa-bowling-ball fa-fw",
            "fa-solid fa-bowl-rice fa-fw",
            "fa-solid fa-bowl-food fa-fw",
            "fa-solid fa-bottle-water fa-fw",
            "fa-solid fa-bottle-droplet fa-fw",
            "fa-solid fa-bore-hole fa-fw",
            "fa-solid fa-border-top-left fa-fw",
            "fa-solid fa-border-all fa-fw",
            "fa-solid fa-book-tanakh fa-fw",
            "fa-solid fa-book-skull fa-fw",
            "fa-solid fa-book-quran fa-fw",
            "fa-solid fa-book-open-reader fa-fw",
            "fa-solid fa-book-open fa-fw",
            "fa-solid fa-book-medical fa-fw",
            "fa-solid fa-book-journal-whills fa-fw",
            "fa-solid fa-book-bookmark fa-fw",
            "fa-solid fa-book-bible fa-fw",
            "fa-solid fa-book-atlas fa-fw",
            "fa-solid fa-bong fa-fw",
            "fa-solid fa-bone fa-fw",
            "fa-solid fa-bolt-lightning fa-fw",
            "fa-solid fa-bold fa-fw",
            "fa-solid fa-blender-phone fa-fw",
            "fa-solid fa-blender fa-fw",
            "fa-solid fa-bitcoin-sign fa-fw",
            "fa-solid fa-biohazard fa-fw",
            "fa-solid fa-bezier-curve fa-fw",
            "fa-solid fa-bell-slash fa-fw",
            "fa-solid fa-bell-concierge fa-fw",
            "fa-solid fa-beer-mug-empty fa-fw",
            "fa-solid fa-bed-pulse fa-fw",
            "fa-solid fa-bed fa-fw",
            "fa-solid fa-battery-three-quarters fa-fw",
            "fa-solid fa-battery-quarter fa-fw",
            "fa-solid fa-battery-half fa-fw",
            "fa-solid fa-battery-full fa-fw",
            "fa-solid fa-battery-empty fa-fw",
            "fa-solid fa-basketball fa-fw",
            "fa-solid fa-basket-shopping fa-fw",
            "fa-solid fa-baseball-bat-ball fa-fw",
            "fa-solid fa-baseball fa-fw",
            "fa-solid fa-bars-staggered fa-fw",
            "fa-solid fa-bars-progress fa-fw",
            "fa-solid fa-bangladeshi-taka-sign fa-fw",
            "fa-solid fa-bandage fa-fw",
            "fa-solid fa-ban-smoking fa-fw",
            "fa-solid fa-ban fa-fw",
            "fa-solid fa-baht-sign fa-fw",
            "fa-solid fa-bahai fa-fw",
            "fa-solid fa-bacterium fa-fw",
            "fa-solid fa-bacteria fa-fw",
            "fa-solid fa-bacon fa-fw",
            "fa-solid fa-backward-step fa-fw",
            "fa-solid fa-backward-fast fa-fw",
            "fa-solid fa-baby-carriage fa-fw",
            "fa-solid fa-b fa-fw",
            "fa-solid fa-austral-sign fa-fw",
            "fa-solid fa-atom fa-fw",
            "fa-solid fa-at fa-fw",
            "fa-solid fa-asterisk fa-fw",
            "fa-solid fa-arrows-up-to-line fa-fw",
            "fa-solid fa-arrows-up-down-left-right fa-fw",
            "fa-solid fa-arrows-up-down fa-fw",
            "fa-solid fa-arrows-turn-to-dots fa-fw",
            "fa-solid fa-arrows-turn-right fa-fw",
            "fa-solid fa-arrows-to-eye fa-fw",
            "fa-solid fa-arrows-to-dot fa-fw",
            "fa-solid fa-arrows-to-circle fa-fw",
            "fa-solid fa-arrows-split-up-and-left fa-fw",
            "fa-solid fa-arrows-spin fa-fw",
            "fa-solid fa-arrows-rotate fa-fw",
            "fa-solid fa-arrows-left-right-to-line fa-fw",
            "fa-solid fa-arrows-left-right fa-fw",
            "fa-solid fa-arrows-down-to-people fa-fw",
            "fa-solid fa-arrows-down-to-line fa-fw",
            "fa-solid fa-arrow-up-z-a fa-fw",
            "fa-solid fa-arrow-up-wide-short fa-fw",
            "fa-solid fa-arrow-up-short-wide fa-fw",
            "fa-solid fa-arrow-up-right-dots fa-fw",
            "fa-solid fa-arrow-up-long fa-fw",
            "fa-solid fa-arrow-up-from-water-pump fa-fw",
            "fa-solid fa-arrow-up-from-ground-water fa-fw",
            "fa-solid fa-arrow-up-a-z fa-fw",
            "fa-solid fa-arrow-up-9-1 fa-fw",
            "fa-solid fa-arrow-up-1-9 fa-fw",
            "fa-solid fa-arrow-turn-up fa-fw",
            "fa-solid fa-arrow-turn-down fa-fw",
            "fa-solid fa-arrow-trend-up fa-fw",
            "fa-solid fa-arrow-trend-down fa-fw",
            "fa-solid fa-arrow-rotate-right fa-fw",
            "fa-solid fa-arrow-rotate-left fa-fw",
            "fa-solid fa-arrow-right-to-city fa-fw",
            "fa-solid fa-arrow-right-to-bracket fa-fw",
            "fa-solid fa-arrow-right-long fa-fw",
            "fa-solid fa-arrow-right-from-bracket fa-fw",
            "fa-solid fa-arrow-right-arrow-left fa-fw",
            "fa-solid fa-arrow-pointer fa-fw",
            "fa-solid fa-arrow-left-long fa-fw",
            "fa-solid fa-arrow-down-z-a fa-fw",
            "fa-solid fa-arrow-down-wide-short fa-fw",
            "fa-solid fa-arrow-down-up-lock fa-fw",
            "fa-solid fa-arrow-down-up-across-line fa-fw",
            "fa-solid fa-arrow-down-short-wide fa-fw",
            "fa-solid fa-arrow-down-long fa-fw",
            "fa-solid fa-arrow-down-a-z fa-fw",
            "fa-solid fa-arrow-down-9-1 fa-fw",
            "fa-solid fa-arrow-down-1-9 fa-fw",
            "fa-solid fa-archway fa-fw",
            "fa-solid fa-apple-whole fa-fw",
            "fa-solid fa-ankh fa-fw",
            "fa-solid fa-angles-up fa-fw",
            "fa-solid fa-angles-right fa-fw",
            "fa-solid fa-angles-left fa-fw",
            "fa-solid fa-angles-down fa-fw",
            "fa-solid fa-angle-right fa-fw",
            "fa-solid fa-angle-left fa-fw",
            "fa-solid fa-angle-down fa-fw",
            "fa-solid fa-anchor-lock fa-fw",
            "fa-solid fa-anchor-circle-xmark fa-fw",
            "fa-solid fa-anchor-circle-exclamation fa-fw",
            "fa-solid fa-anchor-circle-check fa-fw",
            "fa-solid fa-align-right fa-fw",
            "fa-solid fa-align-left fa-fw",
            "fa-solid fa-align-justify fa-fw",
            "fa-solid fa-align-center fa-fw",
            "fa-solid fa-a fa-fw",
            "fa-solid fa-9 fa-fw",
            "fa-solid fa-8 fa-fw",
            "fa-solid fa-7 fa-fw",
            "fa-solid fa-6 fa-fw",
            "fa-solid fa-5 fa-fw",
            "fa-solid fa-4 fa-fw",
            "fa-solid fa-3 fa-fw",
            "fa-solid fa-2 fa-fw",
            "fa-solid fa-1 fa-fw",
            "fa-solid fa-0 fa-fw",
            "fa-regular fa-window-minimize fa-fw",
            "fa-regular fa-window-maximize fa-fw",
            "fa-regular fa-trash-can fa-fw",
            "fa-regular fa-star-half-stroke fa-fw",
            "fa-regular fa-star-half fa-fw",
            "fa-regular fa-square-plus fa-fw",
            "fa-regular fa-square-full fa-fw",
            "fa-regular fa-square-caret-up fa-fw",
            "fa-regular fa-square-caret-right fa-fw",
            "fa-regular fa-square-caret-left fa-fw",
            "fa-regular fa-square-caret-down fa-fw",
            "fa-regular fa-rectangle-xmark fa-fw",
            "fa-regular fa-rectangle-list fa-fw",
            "fa-regular fa-paste fa-fw",
            "fa-regular fa-object-ungroup fa-fw",
            "fa-regular fa-object-group fa-fw",
            "fa-regular fa-note-sticky fa-fw",
            "fa-regular fa-money-bill-1 fa-fw",
            "fa-regular fa-message fa-fw",
            "fa-regular fa-map fa-fw",
            "fa-regular fa-life-ring fa-fw",
            "fa-regular fa-id-card fa-fw",
            "fa-regular fa-id-badge fa-fw",
            "fa-regular fa-hourglass-half fa-fw",
            "fa-regular fa-hourglass fa-fw",
            "fa-regular fa-hard-drive fa-fw",
            "fa-regular fa-hand-spock fa-fw",
            "fa-regular fa-hand-scissors fa-fw",
            "fa-regular fa-hand-pointer fa-fw",
            "fa-regular fa-hand-point-right fa-fw",
            "fa-regular fa-hand-point-left fa-fw",
            "fa-regular fa-hand-point-down fa-fw",
            "fa-regular fa-hand-peace fa-fw",
            "fa-regular fa-hand-lizard fa-fw",
            "fa-regular fa-hand-back-fist fa-fw",
            "fa-regular fa-gem fa-fw",
            "fa-regular fa-futbol fa-fw",
            "fa-regular fa-font-awesome fa-fw",
            "fa-regular fa-folder-closed fa-fw",
            "fa-regular fa-floppy-disk fa-fw",
            "fa-regular fa-file-zipper fa-fw",
            "fa-regular fa-file-video fa-fw",
            "fa-regular fa-file-powerpoint fa-fw",
            "fa-regular fa-file-lines fa-fw",
            "fa-regular fa-file-image fa-fw",
            "fa-regular fa-file-code fa-fw",
            "fa-regular fa-file-audio fa-fw",
            "fa-regular fa-face-tired fa-fw",
            "fa-regular fa-face-surprise fa-fw",
            "fa-regular fa-face-smile-wink fa-fw",
            "fa-regular fa-face-smile-beam fa-fw",
            "fa-regular fa-face-sad-tear fa-fw",
            "fa-regular fa-face-sad-cry fa-fw",
            "fa-regular fa-face-rolling-eyes fa-fw",
            "fa-regular fa-face-meh-blank fa-fw",
            "fa-regular fa-face-meh fa-fw",
            "fa-regular fa-face-laugh-wink fa-fw",
            "fa-regular fa-face-laugh-squint fa-fw",
            "fa-regular fa-face-laugh-beam fa-fw",
            "fa-regular fa-face-laugh fa-fw",
            "fa-regular fa-face-kiss-wink-heart fa-fw",
            "fa-regular fa-face-kiss-beam fa-fw",
            "fa-regular fa-face-kiss fa-fw",
            "fa-regular fa-face-grin-wink fa-fw",
            "fa-regular fa-face-grin-wide fa-fw",
            "fa-regular fa-face-grin-tongue-wink fa-fw",
            "fa-regular fa-face-grin-tongue-squint fa-fw",
            "fa-regular fa-face-grin-tongue fa-fw",
            "fa-regular fa-face-grin-tears fa-fw",
            "fa-regular fa-face-grin-stars fa-fw",
            "fa-regular fa-face-grin-squint-tears fa-fw",
            "fa-regular fa-face-grin-squint fa-fw",
            "fa-regular fa-face-grin-hearts fa-fw",
            "fa-regular fa-face-grin-beam-sweat fa-fw",
            "fa-regular fa-face-grin-beam fa-fw",
            "fa-regular fa-face-grin fa-fw",
            "fa-regular fa-face-grimace fa-fw",
            "fa-regular fa-face-frown-open fa-fw",
            "fa-regular fa-face-frown fa-fw",
            "fa-regular fa-face-flushed fa-fw",
            "fa-regular fa-face-dizzy fa-fw",
            "fa-regular fa-face-angry fa-fw",
            "fa-regular fa-envelope-open fa-fw",
            "fa-regular fa-comment-dots fa-fw",
            "fa-regular fa-circle-stop fa-fw",
            "fa-regular fa-circle-right fa-fw",
            "fa-regular fa-circle-question fa-fw",
            "fa-regular fa-circle-play fa-fw",
            "fa-regular fa-circle-pause fa-fw",
            "fa-regular fa-circle-left fa-fw",
            "fa-regular fa-circle-dot fa-fw",
            "fa-regular fa-chess-rook fa-fw",
            "fa-regular fa-chess-queen fa-fw",
            "fa-regular fa-chess-pawn fa-fw",
            "fa-regular fa-chess-knight fa-fw",
            "fa-regular fa-chess-king fa-fw",
            "fa-regular fa-chess-bishop fa-fw",
            "fa-regular fa-chart-bar fa-fw",
            "fa-regular fa-calendar-xmark fa-fw",
            "fa-regular fa-calendar-plus fa-fw",
            "fa-regular fa-calendar-minus fa-fw",
            "fa-regular fa-calendar-check fa-fw",
            "fa-regular fa-bell-slash fa-fw"
        ];
    }

}
// ==================== #endregion Function ==================== //

// ==================== #region handle dataTableJS ==================== //
class PolyDataTable {
    /**
     * Filter the displayed data on the data table based on the array keyword list arrKeywords
     * @param {DataTable} table the table element
     * @param {Array} arrKeywords an array of search keywords
     */
    static dataByKeywords(table, arrKeywords = []) {
        var regex = '\\b(?:' + arrKeywords.join('|') + ')\\b';
        table.column().search(regex, true, false).draw();
    }
}
// ==================== #endregion handle dataTableJS ==================== //

// ==================== #region handle request ==================== //
class PolyHandleRequest {
    static ajaxComplete(callbackFunction, argsArray = null) {
        $(document).ajaxComplete(function (event, xhr, settings) {
            if (argsArray != null) {
                argsArray.forEach(args => {
                    callbackFunction(...args);
                });
            } else {
                callbackFunction();
            }
        });
    }
}
// ==================== #endregion handle request ==================== //

// ==================== #region handle operation functions ==================== //
class PolyOperationFunctions {

    //Accept hotkey Ctrl+S
    static patternAcceptHotkeyCtrlS = [
        { "pattern": "^.*?/article/\\d(.*?)+$", "submit": '.btn.btn-primary' },
        { "pattern": "^.*?/admin/projects/project/\\d(.*?)+$", "submit": '.btn.btn-primary' },
        { "pattern": "^.*?/admin/contracts/contract/\\d(.*?)+$", "submit": '.btn.btn-primary' },
        { "pattern": "^.*?/admin/poly_utilities/(.*?)_add(.*?)+$", "submit": '.btn.btn-primary' },
        { "pattern": "^.*?/admin/projects/view/(.*?)group=project_notes(.*?)+$", "submit": 'form[action*="/admin/projects/save_note/"] .btn.btn-primary' }
    ];

    static BeforeInit = () => {
        document.addEventListener('DOMContentLoaded', function () {
            const headerElement = document.getElementById('header');
            if (headerElement) {
                if (typeof poly_utilities_settings !== 'undefined') {
                    poly_utilities_settings.topbar_height = headerElement.offsetHeight;
                }
            }
        });
    }

    static DisableReload = () => {
        // ==== Disable confirm reload: Utilize on pages where disabling page reload requests is required. ==== //
        window.addEventListener('beforeunload', e => {
            window.onbeforeunload = null;
            e.stopImmediatePropagation();
        });

        addEventListener('beforeunload', event => { });
        onbeforeunload = event => { };
    }

    static Init = () => {

        //Operations back content edit
        var previousPage = localStorage.getItem('previousPage');
        if (previousPage && previousPage === 'true') {
            localStorage.setItem('previousPage', 'false');
            window.history.back();
        }

        //Copy elements
        try {
            new ClipboardJS('.poly-copy');
        } catch ($ex) { }

        //Scroll to top
        $('body').append('<div class="poly-utilities-widgets not-selectable"><div class="poly-scroll-to-top"><i class="fas fa-arrow-circle-up"></i></div></div>');

        //Handle active PolyUtilities menu
        if (window.location.href.indexOf("poly_utilities/scripts") > -1) {
            $(".sub-menu-item-poly_utilities_scripts_extend").addClass("active");
        }
        if (window.location.href.indexOf("poly_utilities/styles") > -1) {
            $(".sub-menu-item-poly_utilities_styles_extend").addClass("active");
        }

    }

    static Copy = (content, iconClass = '') => {
        let anchor = `${iconClass !== '' ? `<i class='${iconClass}'>&nbsp;</i>` : 'Copy'}`;
        return `<span class="poly-copy" data-clipboard-text="${content}" data-toggle="tooltip" data-original-title="Copy">${anchor}</span>`
    }

    static AHref = (content, iconClass = '', title = '') => {
        let anchor = `${iconClass !== '' ? `<i class='${iconClass}'>&nbsp;</i>` : 'Blank'}`;
        return `<span class="poly-table-icon"><a href="${content}" title="${title}" target="_blank">${anchor}</a></span>`
    }
    static Widgets = (mode = 'admin') => {
        if (mode === 'admin') {
            let isAdmin = '.app.admin';
        
            // Dashboard
            if (poly_utilities_settings.widgets['poly-area-before-dashboard']) {
                poly_utilities_settings.widgets['poly-area-before-dashboard'].forEach(element => {
                    $(`${isAdmin}.dashboard #wrapper .content`).before(`<div class="poly-widgets-block">${element}</div>`);
                });
            }

            // Avatar
            if (poly_utilities_settings.widgets['poly-area-right-avatar']) {
                poly_utilities_settings.widgets['poly-area-right-avatar'].forEach(element => {
                    $(`${isAdmin} .nav.navbar-nav.navbar-right`).prepend(`<li><div class="poly-widgets-block">${element}</div></li>`);
                });
            }
            if (poly_utilities_settings.widgets['poly-area-after-avatar']) {
                poly_utilities_settings.widgets['poly-area-after-avatar'].forEach(element => {
                    $(`${isAdmin} .nav.navbar-nav.navbar-right .header-user-profile .dropdown-toggle.profile`).after(`<span class="poly-widget-after-avatar"><div class="poly-widgets-block">${element}</div></span>`);
                });
            }

            // Sidebar menu
            if (poly_utilities_settings.widgets['poly-area-before-sidebar-logo']) {
                poly_utilities_settings.widgets['poly-area-before-sidebar-logo'].forEach(element => {
                    $(`${isAdmin} #logo`).before(`<div class="poly-widgets-block">${element}</div>`);
                });
            }

            if (poly_utilities_settings.widgets['poly-area-before-sidebar-menu']) {
                poly_utilities_settings.widgets['poly-area-before-sidebar-menu'].forEach(element => {
                    $(`${isAdmin} #logo`).after(`<div class="poly-widgets-block">${element}</div>`);
                });
            }

            if (poly_utilities_settings.widgets['poly-area-after-sidebar-menu']) {
                poly_utilities_settings.widgets['poly-area-after-sidebar-menu'].forEach(element => {
                    $(`${isAdmin} #side-menu`).after(`<div class="poly-widgets-block">${element}</div>`);
                });
            }

            // Topbar menu
            if (poly_utilities_settings.widgets['poly-area-after-main-menu']) {
                poly_utilities_settings.widgets['poly-area-after-main-menu'].forEach(element => {
                    $(`${isAdmin} #wrapper .content`).before(`<div class="poly-widgets-block">${element}</div>`);
                });
            }

            // Setup menu
            if (poly_utilities_settings.widgets['poly-area-before-setup-menu']) {
                poly_utilities_settings.widgets['poly-area-before-setup-menu'].forEach(element => {
                    $(`${isAdmin} #setup-menu`).before(`<div class="poly-widgets-block">${element}</div>`);
                });
            }
            if (poly_utilities_settings.widgets['poly-area-after-setup-menu']) {
                poly_utilities_settings.widgets['poly-area-after-setup-menu'].forEach(element => {
                    $(`${isAdmin} #setup-menu`).append(`<div class="poly-widgets-block">${element}</div>`);
                });
            }

            // Footer admin
            if (poly_utilities_settings.widgets['poly-area-footer']) {
                poly_utilities_settings.widgets['poly-area-footer'].forEach(element => {
                    $(`${isAdmin} #wrapper`).after(`<div class="poly-widgets-block">${element}</div>`);
                });
            }
        }
        if (mode === 'user') {
            let isCustomers = '.customers';

            // Article
            if (poly_utilities_settings.widgets['poly-area-before-dashboard']) {
                poly_utilities_settings.widgets['poly-area-before-article-details'].forEach(element => {
                    $(`${isCustomers} .section-knowledge-base h1.kb-article-single-heading`).after(`<div class="poly-widgets-block">${element}</div>`);
                });
            }

            if (poly_utilities_settings.widgets['poly-area-after-article-details']) {
                poly_utilities_settings.widgets['poly-area-after-article-details'].forEach(element => {

                    let $pTags = $(`${isCustomers} .section-knowledge-base .tc-content.kb-article-content`).find('p');
                    if ($pTags.length > 0 && $pTags.length > 4) {
                        let middleIndex = Math.floor($pTags.length / 2);
                        $pTags.eq(middleIndex).after(`<div class="poly-widgets-block">${element}</div>`);
                    }

                    $(`${isCustomers} .section-knowledge-base .kb-article-content`).after(`<div class="poly-widgets-block">${element}</div>`);
                });
            }

            // Footer
            if (poly_utilities_settings.widgets['poly-area-customers-footer']) {
                poly_utilities_settings.widgets['poly-area-customers-footer'].forEach(element => {
                    $(`${isCustomers} footer.footer`).before(`<div class="poly-widgets-block">${element}</div>`);
                });
            }

            // Customer login form
            if (poly_utilities_settings.widgets['poly-area-customers-before-login']) {
                poly_utilities_settings.widgets['poly-area-customers-before-login'].forEach(element => {
                    $(`.customers.customers_login form .panel-body`).prepend(`<div class="poly-widgets-block">${element}</div>`);
                });
            }
            if (poly_utilities_settings.widgets['poly-area-customers-after-login']) {
                poly_utilities_settings.widgets['poly-area-customers-after-login'].forEach(element => {
                    $(`.customers.customers_login form .panel-body`).append(`<div class="poly-widgets-block">${element}</div>`);
                });
            }

            // Admin login form
            setTimeout(() => {
                $(`.authentication-form-wrapper`).removeClass('tw-pt-24');
                if (poly_utilities_settings.widgets['poly-area-admin-before-login']) {
                        poly_utilities_settings.widgets['poly-area-admin-before-login'].forEach(element => {
                            $(`.login_admin form`).before(`<div class="poly-widgets-block">${element}</div>`);
                        });
                }
                if (poly_utilities_settings.widgets['poly-area-admin-after-login']) {
                    poly_utilities_settings.widgets['poly-area-admin-after-login'].forEach(element => {
                        $(`.login_admin form`).after(`<div class="poly-widgets-block">${element}</div>`);
                    });
                }
            }, 50);
            
            //Hook widgets
            if (typeof poly_utilities_settings.widgets_hook !== 'undefined') {
                let areas = poly_utilities_settings.widgets_hook;
                Object.keys(areas).forEach(area => {
                    let widget_area = $(PolyWidgets.DynamicWidget(`${area}`));
                    if (widget_area.length!=0){
                        if (areas[area].length > 0) {
                            areas[area].forEach(content => {
                                widget_area.after(`<div class="poly-widgets-block">${content}</div>`);
                            });
                        }
                    }
                });
            }
        }
    }
    static ScrollToTop = (handleByClass) => {
        handleByClass.forEach(selector => {
            $(selector).css({ 'display': 'block' });
            $(document).on('click', selector, function () {
                $('html, body').animate({
                    scrollTop: 0
                }, 'slow');
            });
        });
    }

    static isAcceptHotkeyCtrlS = () => {
        let currentURL = window.location;
        for (let i = 0; i < this.patternAcceptHotkeyCtrlS.length; i++) {
            let pattern = new RegExp(this.patternAcceptHotkeyCtrlS[i].pattern);
            if (pattern.test(currentURL)) {
                return this.patternAcceptHotkeyCtrlS[i];
            }
        }
        return null;
    }

    /**
    * Display a popup to choose icons or input custom class, SVG, HTML information.
    * @param {string} element class name or ID of the element to display the popup when clicked.
    */
    static Icons = (element) => {
        let iconClasses = PolyCommon.fontAwesome();
        var iconsContent = `<div class="row poly-utilities-icons-modal-container">
        <div class="text-center">
            <i class="preview fa-solid fa-shield-halved fa-fw fa-2x"></i>
        </div>
        <div class="col-md-12">
            <input type="text" data-id="_POLYDATAID_" class="poly-utilities-icon-search form-control" placeholder="font-awesome" aria-label="font-awesome" aria-describedby="basic-addon1">
        </div>
        </div>
        <div class="poly-utilities-icons-modal">`;
        iconClasses.forEach(function (iconClass) {
            iconsContent += `<div class="text-center icon"><div class="wrap"><i data-id="_POLYDATAID_" class="fa-2x ${iconClass}"></i></div></div>`;
        });
        iconsContent += '</div>';

        let modals_icons = poly_utilities_settings.modals.find(item => 'icons' in item)['icons'];

        $(element).each(function () {
            let curElement = $(this);
            let itemID = curElement.data('id');

            if (itemID === undefined) { return; }

            let curParentElement = $(`#${itemID}`);

            curElement.off('click').on('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: modals_icons.header,
                    customClass: {
                        container: 'poly-utilities-icon-modal',
                    },
                    html: `<div>${iconsContent}
                    <input class="poly-utilities-icon-value form-control" type="text" value=""/>
                    ${PolyMessage.displayMessage(modals_icons.message)}
                </div>`,
                    showConfirmButton: true,
                    showCancelButton: true,
                    showCloseButton: true,
                    allowOutsideClick: false,
                    confirmButtonColor: modals_icons.confirm_color,
                    cancelButtonColor: modals_icons.cancel_color,
                    confirmButtonText: modals_icons.confirm,
                    cancelButtonText: modals_icons.cancel,
                    focusConfirm: false,
                    didOpen: function () {
                        let value = curElement.html().trim();
                        var result = (value.indexOf('<i') !== -1) ? curElement.find('i').attr('class') : value;
                        $('.poly-utilities-icon-value').val(result.trim());
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        let value = $('.poly-utilities-icon-value').val().trim();
                        var result = (value.indexOf('<') !== -1) ? value : `<i class="${value}"></i>`;
                        curParentElement.find('.poly_aio_supports_icon').val(value);
                        curParentElement.find('.poly-utilities-aio-icon-select').html(result);
                    }
                });
            });
        });

        $(document).on('change keydown', '.poly-utilities-icon-search', function (event) {
            "use strict";
            var searchValue = $(this).val().toLowerCase();
            var filteredIcons = PolyCommon.fontAwesomeBySearchKeywords(searchValue, iconClasses);

            var filteredContent = '';
            filteredIcons.forEach(function (iconClass) {
                filteredContent += '<div class="text-center icon"><div class="wrap"><i data-id="_POLYDATAID_" class="fa-2x ' + iconClass + '"></i></div></div>';
            });
            if (filteredContent !== '') {
                let dataId = $(this).attr('data-id');
                filteredContent = filteredContent.replace(/_POLYDATAID_/g, dataId);
                $('.poly-utilities-icons-modal').html(filteredContent);
            }
        });

        $(document).on('click', '.poly-utilities-icons-modal .icon i', function () {
            "use strict";
            var selectedIconClass = $(this).attr('class').trim();
            let iconSelected = selectedIconClass.replace(/\bfa-2x\b/g, '').trim();

            $('.poly-utilities-icons-modal-container .preview').attr('class', `preview ${selectedIconClass}`);
            $(`.poly-utilities-icon-value`).val(iconSelected);
        });
    }

    /**
     * Display support contact list
     * @param {Array} data Array of contact information list (json objects)
     */
    static SupportLine = (data) => {
        let renderHTML = '';
        data.forEach(item => {
            let renderByType = '';
            let icon = item.icon;
            if (icon && icon.indexOf('<') == -1) {
                icon = `<i class="${icon}"></i>`;
            }
            let icon_color = `color:${item.icon_color}!important;font-size:48px`;
            switch (item.type) {
                case 'link': {
                    renderByType = `<a href="${item.content ? item.content : '#'}" rel="nofollow" target="_blank"><span class="icon" style="${icon_color}">${icon}</span>${item.title}</a>`;
                    break;
                }
                case 'mobile': {
                    renderByType = `<a href="${item.content ? 'tel:' + item.content : '#'}" rel="nofollow" target="_blank"><span class="icon" style="${icon_color}">${icon}</span>${item.title}</a>`;
                    break;
                }
                case 'whatsapp': {
                    renderByType = `<a href="${item.content ? 'https://web.whatsapp.com/send?phone=' + item.content : '#'}" rel="nofollow" target="_blank"><span class="icon" style="${icon_color}">${icon}</span>${item.title}</a>`;
                    break;
                }
                case 'viber': {
                    renderByType = `<a href="${item.content ? 'viber://chat?number=' + item.content : '#'}" rel="nofollow" target="_blank"><span class="icon" style="${icon_color}">${icon}</span>${item.title}</a>`;
                    break;
                }
                case 'facebook_messenger': {
                    renderByType = `<a href="${item.content ? 'https://m.me/' + item.content : '#'}" rel="nofollow" target="_blank"><span class="icon" style="${icon_color}">${icon}</span>${item.title}</a>`;
                    break;
                }
                case 'email': {
                    renderByType = `<a href="${item.content ? 'mailto:' + item.content : '#'}" rel="nofollow" target="_blank"><span class="icon" style="${icon_color}">${icon}</span>${item.title}</a>`;
                    break;
                }
                default:
                    renderByType = `<a href="${item.content ? item.content : '#'}" rel="nofollow" target="_blank"><span class="icon" style="${icon_color}">${icon}</span>${item.title}</a>`;
                    break;
            }
            renderHTML += `<li>${renderByType}</li>`;
        });
        let hotlineElement = $('.poly-utilities-widgets');

        let renderMessage = (poly_utilities_settings.aio_supports.is_messages === 'true') ? `<div class="prompt show"><div class="prompt-close"><svg width="12" height="13" viewBox="0 0 14 14" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="Canvas" transform="translate(-4087 108)"><g id="Vector"><use xlink:href="#path0_fill" transform="translate(4087 -108)" fill="currentColor"></use></g></g><defs><path id="path0_fill" d="M 14 1.41L 12.59 0L 7 5.59L 1.41 0L 0 1.41L 5.59 7L 0 12.59L 1.41 14L 7 8.41L 12.59 14L 14 12.59L 8.41 7L 14 1.41Z"></path></defs></svg></div>
        <div class="prompt-content"></div></div>`: '';

        hotlineElement.prepend(`<div class="support">${renderMessage}
                            <div class="button" style="background:${poly_utilities_settings.aio_supports.icon_button_color}"><i class="active ${poly_utilities_settings.aio_supports.icon_button}"></i><i class="deactive fas fa-times"></i></div>
                            <div class="line"><span class="close"><i class="fa-solid fa-xmark fa-fw"></i></span><ul>${renderHTML}</ul></div>
                        </div>`);

        if (poly_utilities_settings.aio_supports.is_messages === 'true') {
            let sentences = poly_utilities_settings.aio_supports.messages ?? [];
            if (poly_utilities_settings.aio_supports.is_messages_mobile !== 'true') {
                $('.poly-utilities-widgets .prompt').addClass('hidden-xs');
            }
            PolyCommon.displayContentBySentences($('.poly-utilities-widgets .prompt .prompt-content'), sentences);
        }

        let lineElementButton = hotlineElement.find('.button');
        hotlineElement.on('click', '.support', function () {
            "use strict";
            let lineElement = $(this).find('.line');
            if (lineElement.hasClass('show')) {
                lineElement.fadeOut(200, function () {
                    lineElement.removeClass('show');
                    lineElementButton.removeClass('show');
                });
            } else {
                lineElement.addClass('show').fadeIn();
                lineElementButton.addClass('show');
            }
        });

        hotlineElement.on('click', '.close', function (event) {
            "use strict";
            event.stopPropagation();
            $(this).closest('.line').fadeOut(200, function () {
                $(this).removeClass('show');
                lineElementButton.removeClass('show');
            });
        });

        let lineElementButtonPromptClose = hotlineElement.find('.prompt');
        hotlineElement.on('click', '.prompt-close', function (event) {
            "use strict";
            event.stopPropagation();
            lineElementButtonPromptClose.removeClass('show');
        });

        $(document).on('click', function (event) {
            "use strict";
            const target = $(event.target);
            if (!target.closest('.poly-utilities-widgets').length) {
                $('.poly-utilities-widgets .line.show').fadeOut(200, function () {
                    $(this).removeClass('show');
                    lineElementButton.removeClass('show');
                });
            }
        });
    }
}
PolyOperationFunctions.BeforeInit();
// ==================== #endregion handle operation functions ==================== //