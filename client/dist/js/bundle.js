(function($) {
    $.entwine("ss", function($) {
        $(".ss-gridfield.ss-gridfield-editable.uf-field-editor").entwine({
            onaddnewinlinenamedtemplate: function(e, templateName) {
                if(e.target != this[0]) {
                    return;
                }

                var tmpl = window.tmpl;
                var row = this.find('.ss-gridfield-add-inline-template[data-name="' + templateName + '"]:last');
                var num = this.data("add-inline-num") || 1;

                tmpl.cache[this[0].id + "ss-gridfield-add-inline-template-" + templateName] = tmpl(row.html());

                this.find("tbody:first").append(tmpl(this[0].id + "ss-gridfield-add-inline-template-" + templateName, { num: num }));
                this.find("tbody:first").children(".ss-gridfield-no-items").hide();
                this.data("add-inline-num", num + 1);

                // Rebuild sort order fields
                $(".ss-gridfield-orderable tbody").rebuildSort();
            }
        });

        $(".ss-gridfield-add-new-form-field-inline").entwine({
            onclick: function() {
                this.getGridField().trigger("addnewinlinenamedtemplate", 'form-field');
                return false;
            }
        });

        $(".ss-gridfield-add-new-field-group-inline").entwine({
            onclick: function() {
                this.getGridField().trigger("addnewinlinenamedtemplate", 'field-group');
                return false;
            }
        });

        $(".ss-gridfield-add-new-page-break-inline").entwine({
            onclick: function() {
                this.getGridField().trigger("addnewinlinenamedtemplate", 'page-break');
                return false;
            }
        });

        $('.grid-field .action.grid-field__clear-submissions').entwine({
            onclick: function (e) {
                const confirmMessage = 'Are you sure you want to delete all form submissions? This cannot be undone';

                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return false;
                } else {
                    this._super(e);
                }
            }
        });
    });
})(jQuery);
