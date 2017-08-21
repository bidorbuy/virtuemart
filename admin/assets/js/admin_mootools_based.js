window.addEvent('domready', function () {

    var input = '.tokenurl';
    var ccm = 'ctrl-c-message';
    var cb = '.copy-button';
    var copyButton = new Element('button', {'class': 'button copy-button', type: 'button'}).appendText('Copy');
    var launchButton = new Element('button', {'class': 'button launch-button', type: 'button'}).appendText('Launch');
    //var fieldsetLog = $('loggingForm').getParent().appendChild(new Element('fieldset'));
    //fieldsetLog.appendChild(new Element('legend')).appendText('Debug');
    //fieldsetLog.appendChild($('loggingForm').clone(true, true));
    //$('loggingForm').dispose();

    //Handle Export
    if ($('export')) {
        $('export').parentNode.appendChild(launchButton.clone().set('id','exportBtn'));
        $('exportBtn').addEvent('click',function () {
            window.open($('export').get('value'));
        });
    }

    //Handle Download
    if ($('download')) {
        $('download').parentNode.appendChild(launchButton.clone().set('id','downloadBtn'));
        $('downloadBtn').addEvent('click',function () {
            window.open($('download').get('value'));
        });
    }

    //Handle Reset Audit (Reset export data) link and button
    if ($('resetaudit')) {
        $('resetaudit').parentNode.appendChild(launchButton.clone().set('id','resetAuditBtn'));
        $('resetAuditBtn').addEvent('click',function () {
            window.open($('resetaudit').get('value'));
        });
    }

    //handler for tokens fields - for "Export" and "Download"
    $$(input).each(function (el) {
        el.parentNode.appendChild(copyButton.clone());
        el.addEvents({
            focus: function () {
                this.select();
            },
            click: function () {
                this.select();
            }
        });
    });

    $$(cb).addEvent('click', function () {
        this.getPrevious('input').select();
    });

    $$(cb + "," + input).each(function (el) {
        el.addEvent('click', function (e) {
            $(ccm).style.left = (((e.pageX != undefined) ? e.pageX : e.page.x) + 30) + 'px';
            $(ccm).style.top = ((e.pageY != undefined) ? e.pageY : e.page.y + 30) + 'px';
            $(ccm).style.display = 'block';
            $(ccm).addEvent('mouseenter', function () {
                this.style.display = 'none';
            });
        });
    });


    this.addEvent('keydown', function (event) {
        if (event.key == 'c' && event.control) $(ccm).hide();
    });

    $$('.loggingFormButton').each(function (el) {
        el.addEvent('click', function () {
            $('loggingFormFilename').value = this.getProperty('filename');
            $('loggingFormAction').value = this.getProperty('action');
            $('loggingForm').submit();
        });
    });

    $("include").addEvent('click', function () {
        s = $$('#bobsi-exc-categories option:selected');
        s.each(function (item) {
            item.dispose();
            new Element('option', {
                value: item.value
            }).appendText(item.text).inject($('bobsi-inc-categories'));
        });
        /*
         * Defect#3734=>3737
         * Set width when press button include;
         */
        setCssWidthItems();
        return false;
    });

    //TODO: check this in IE (which version is required in spec-n)
    $("exclude").addEvent('click', function () {
        s = $$('#bobsi-inc-categories option:selected');
        s.each(function (item) {
            item.dispose();
            (new Element('option', {
                value: item.value
            }).appendText(item.text)).inject($('bobsi-exc-categories'));
        });
        /*
         * Defect#3734=>3737
         * Set width when press button exclude;
         */
        setCssWidthItems();
        return false;
    });

    var savebutton = $('toolbar-publish').getElement('a');
    savebutton.setProperty('onclick', '');
    savebutton.addEvent('click', function () {
        $$('#bobsi-exc-categories option').setProperty('selected', 'selected');
        submitbutton('save');
//        Joomla.submitbutton('save'); //how to automate?
    });

    //start export in new window
    $('toolbar-archive').getElement('a').setProperties({onclick: '', href: $('export').getProperty('value'), target: '_blank'});
    //start download in new window
    $('toolbar-download').getElement('a').setProperties({onclick: '', href: $('download').getProperty('value'), target: '_blank'});
    //Set the same  height for "Export Configurations" and "Export Criteria"
    $$('div.fltrt .panelform').each(function (item_rt) {
        item_rt.setStyle('height', $$('div.fltlft fieldset.panelform')[0].getStyle('height'))
    });


    /*
     * Set width for buttons criteria container.
     */
    $$('p.submit button').each(function (item) {
        maxw = 0;
        if (item.getSize().x>maxw) {
            maxw = item.getSize().x;
        }
    });
    $$('.buttons_criteria').setStyle('width',maxw+8);

    /*
     * Function for fix Defect#3734=>3737
     */
    function setCssWidthItems() {
        /*
         * For all browsers based on Google chrome(Chrome, Opera, Yandex ...)
         */
        if (navigator.userAgent.search(/Chrome/) > 0) {
            $$('select[multiple]').set({
                    styles: {
                        width: '170px',
                        height: '160px',
                        'overflow-x': 'auto'
                    }
                }
            );
            tmd_div = new Element('div', {
                'id': 'tmp_div_container',
                styles: {
                    'visibility': 'hidden',
                    'overflow': 'hidden'
                }
            });
            tmd_div.inject('footer', 'after');
            sel = $$('.bobsi-categories-select option');
            sel.each(function (item) {
                el = new Element('span', {
                    'id': 'option_id_' + item.value,
                    'text': item.text
                });
                el.inject('tmp_div_container', 'top');
                max_width = $('option_id_' + item.value).getSize().x;
                item.setStyle('width', max_width)
            });
            tmd_div.dispose();
            /*
             * For Others browsers etc: Firefox, IE ...
             */
        } else {
            d = new Element('div', {
                'id': 'firefox_wraper',
                styles: {
                    'width': '170px',
                    'height': '160px',
                    'overflow-x': 'auto'
                }
            });

            d2 = new Element('div', {
                'id': 'firefox_wraper2',
                styles: {
                    'width': '170px',
                    'height': '160px',
                    'overflow-x': 'auto'
                }
            });
            d.wraps($('bobsi-inc-categories'));
            d2.wraps($('bobsi-exc-categories'));
        }
    }
    /*
     * Set item width when DOM elements loaded
     */
    setCssWidthItems();

    logs = $('logs');
    debug = $('debug');
    links = $('links');
    links.inject(debug,'before');
    logs.inject(debug,'bottom');
});

function saveButtonClick() {
    savebutton = $('toolbar-publish').getElement('a');
    savebutton.click();
}