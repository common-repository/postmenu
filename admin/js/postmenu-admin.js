(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
	 *
	 * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
	 *
	 * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    //Creating an encapsulate scope for the plugin JavaScript functions.
    window.LP_Scope = window.LP_Scope || {};

    window.LP_Scope = $.extend(window.LP_Scope, {
        editShowMenu: false,
        overEditMenu: false,
        addEditMenuButton: addEditMenuButton,
        //Active the show advanced function for advanced duplicates in post list.
        advancedDuplicatePostListForm: advancedDuplicatePostListForm,
        saveAdvancedDuplicateForm: saveAdvancedDuplicateForm,
        saveShowMenu: false,
        addSaveMenuButton: addSaveMenuButton,
        ableToOpen: true,
        addMenuLinkButton: addMenuLinkButton,
        duplicateMenu: duplicateMenu,
        notifyError: notifyError,
        initEditMenuForm: initEditMenuForm,
        changeSelectedMenu: changeSelectedMenu,
        getMenuItemBox: getMenuItemBox,
        initSortables: initSortables,
        jQueryExtensions: jQueryExtensions,
        depthToPx: depthToPx,
        pxToDepth: pxToDepth,
        refreshAdvancedAccessibilityOfItem: refreshAdvancedAccessibilityOfItem,
        refreshAdvancedAccessibility: refreshAdvancedAccessibility,
        refreshKeyboardAccessibility: refreshKeyboardAccessibility,
    });

    //in order to use this actions buttons outside of initEditMenuForm function, we sould declare here
    var create_menu_action ,add_to_menu_action, select_menu_action,  update_menu_action = null;

    /**
     * Start duplicate post functions
     */
    document.addEventListener("DOMContentLoaded", function () {
        //Trigger this function.
        LP_Scope.advancedDuplicatePostListForm();
    });
    //Create the dropdown menu after the Add New button in edit post.
    function addEditMenuButton(editMenuButtonLinks) {
        var editMenuButton = $('a.page-title-action');
        editMenuButton
            .after(
                '<a class="postmenu-button-menu">+</span></a>' +
                '<div class="postmenu-dropdown-menu"><ul class="postmenu-menu-list">' +
                '<li>' + editMenuButtonLinks['postmenu_duplicate_dropdown_menu'] + '</li>' +
                '<li>' + editMenuButtonLinks['postmenu_duplicate_edit_dropdown_menu'] + '</li>' +
                (editMenuButtonLinks['postmenu_advanced_duplicate_dropdown_menu'] ? '<li>' + editMenuButtonLinks['postmenu_advanced_duplicate_dropdown_menu'] + '</li>' : '') +
                '</ul>' +
                '</div>'
            )
            .css('border-radius', '2px 0 0 2px')
            .on('mouseover', overMenu)
            .on('mouseleave', leaveMenu);
        var editMenu = $('.postmenu-dropdown-menu'),
            buttonMenu = $('.postmenu-button-menu');
        buttonMenu
            .on('mouseover', overMenu)
            .on('mouseleave', leaveMenu);
        function overMenu() {
            buttonMenu.css({'background-color': '#00a0d2', 'color': '#FFF', 'border-color': '#008ec2'});
            editMenuButton.css({'background-color': '#00a0d2', 'color': '#FFF', 'border-color': '#008ec2'});
            LP_Scope.overEditMenu = true;
        }

        function leaveMenu() {
            buttonMenu.css({'background-color': '#F7F7F7', 'color': '#0073aa', 'border-color': '#ccc'});
            editMenuButton.css({'background-color': '#F7F7F7', 'color': '#0073aa', 'border-color': '#ccc'});
            LP_Scope.overEditMenu = false;
        }

        buttonMenu.on('click', function () {
            LP_Scope.editShowMenu = !LP_Scope.editShowMenu;
            if (LP_Scope.editShowMenu) {
                editMenu.show();
                editMenu.css({'left': editMenuButton[0].offsetLeft})
            } else {
                editMenu.hide();
            }
            editMenu.on('mouseover', function () {
                LP_Scope.overEditMenu = true;
            }).on('mouseleave', function () {
                LP_Scope.overEditMenu = false;
                editMenu.hide();
                LP_Scope.editShowMenu = false;
            });
            return false;
        });
        $(document).on('click', function () {
            if (!LP_Scope.overEditMenu) {
                editMenu.hide();
                LP_Scope.editShowMenu = false;
            }
        });
        $(document).on('click', '#postmenu_advanced_duplicate_dropdown_menu', function () {
            $.post(ajaxurl,
                {'action': 'postmenu_advanced_duplicate_fieldsets', 'id': LP_Scope.current_postId, 'rownumber': '0'},
                function (data) {
                    editMenu.after(data);
                });
            return false;
        });
        $(document).on('click', '#postmenu_duplicate_dropdown_menu', function () {
            editMenu.hide();
            $.post(ajaxurl,
                {'action': 'postmenu_ajax_duplicate_post_admin', 'id': LP_Scope.current_postId},
                function () {
                    $("hr.wp-header-end").after('<div id="message" class="updated notice is-dismissible">' +
                        '<p>' + postmenu_success_message + '</p>' +
                        '<button type="button" class="notice-dismiss">' +
                        '<span class="screen-reader-text">Dismiss this notice.</span>' +
                        '</button>' +
                        '</div>');
                    setTimeout(function () {
                        $("#message").remove();
                    }, 5000);
                    $(document).on("click", ".notice-dismiss", function () {
                        $("#message").remove();
                    });
                });
            return false;
        });
    }

    //Active the show advanced function for advanced duplicates in post list.
    function advancedDuplicatePostListForm() {
        function buildAction(action, element) {
            var field = element.closest('tr'),
                selector = '#' + field.id,
                postId = element.id.split('-')[1],
                colspan = ($(selector).find('td').length + $(selector).find('th').length);
            $(selector).hide();
            $('button.button.cancel.alignleft').trigger('click');
            $.post(ajaxurl,
                {'action': action, 'id': postId, 'rownumber': colspan},
                function (data) {
                    $(selector).after(data);
                    LP_Scope.selector = selector;
                });
        }

        $(document).on('click', '.lion_pm_advanced_duplicate_row_link a', function () {
            buildAction('postmenu_advanced_duplicate_fieldsets', this);
        });
        $(document).on('click', '.lion_pm_menu_link_row_link a', function () {
            buildAction('postmenu_menu_link_fieldsets', this);
        });
    }

    function saveAdvancedDuplicateForm(post_copy, callback) {

        post_copy.post_title = $('[name="postmenu_post_title"]').val();
        post_copy.post_name = $('[name="postmenu_post_name"]').val();
        post_copy.post_date = $('[name="postmenu_aa"]').val() + '-' + $('[name="postmenu_mm"]').val() + '-' + $('[name="postmenu_dd"]').val() +
            ' ' + $('[name="postmenu_hh"]').val() + ':' + $('[name="postmenu_mn"]').val() + ':' + $('[name="postmenu_ss"]').val();
        post_copy.post_password = $('[name="postmenu_post_password"]').val();
        post_copy.post_status = $('[name="postmenu_post_status"]').val();
        post_copy.post_status = $('[name="postmenu_keep_private"]').attr("checked") ? 'private' : post_copy.post_status;
        post_copy.comment_status = $('[name="postmenu_comment_status"]').attr("checked") ? 'open' : 'closed';
        post_copy.comment_status = $('[name="postmenu_ping_status"]').attr("checked") ? 'open' : 'closed';

        var post_categories = $('[name="postmenu_post_categories"]'),
            post_parent = $('[name="postmenu_post_parent"]'),
            post_tags = $('#postmenu_post_tags'),
            is_sticky = $('[name="postmenu_sticky"]');

        var conditions = [
            {name: 'advanced', value: 1}
        ];

        $('.postmenu_default_properties_to_copy').each(function () {
            if (!this.checked && post_copy[this.name] !== undefined) {
                post_copy[this.name] = '';
            }
            if (this.checked && post_copy[this.name] === undefined) {
                conditions.push({
                    name: this.name,
                    value: 1
                })
            }
        });

        if (post_categories.length > 0) {
            var categories = {
                name: 'post_categories',
                value: []
            };
            post_categories.each(function () {
                if (this.checked) {
                    categories.value.push(this.value);
                }
            });
            conditions.push(categories);
        }

        if (post_parent.length > 0) {
            post_copy.post_parent = post_parent.val();
            post_copy.menu_order = $('[name="postmenu_menu_order"]').val();
        }

        if (post_tags.length > 0 && post_tags.val()) {
            conditions.push({
                name: 'post_tags',
                value: post_tags.val(),
            });
        }

        if (is_sticky.length > 0) {
            conditions.push({
                name: 'is_sticky',
                value: is_sticky.attr("checked") ? 1 : 0
            });
        }

        var params = {
            post_copy: post_copy,
            conditions: conditions
        };

        $.post(ajaxurl,
            {'action': 'postmenu_ajax_advanced_duplicate_post_admin', 'params': params},
            function () {
                callback();
            });
    }

    /**
     * End duplicate post functions
     * Start duplicate menu functions
     */
    function addSaveMenuButton() {
        $('.publishing-action')
            .append(
                '<a class="postmenu-save-button-menu">+</span></a>' +
                '<div class="postmenu-dropdown-menu contained"><ul class="postmenu-menu-list">' +
                '<li>' + LP_Scope.save_menu_button_links['duplicate'] + '</li>' +
                '<li>' + LP_Scope.save_menu_button_links['duplicate_edit'] + '</li>' +
                '</ul>' +
                '</div>')
            .on('mouseover', function () {
                LP_Scope.overEditMenu = true;
            })
            .on('mouseleave', function () {
                LP_Scope.overEditMenu = false;
            });
        $('.publishing-action input[type="submit"]').css('border-radius', '3px 0 0 3px');

        $(document).on('click', function () {
            if (!LP_Scope.overEditMenu) {
                $('.postmenu-dropdown-menu.contained').hide();
                LP_Scope.saveShowMenu = false;
            }
        });
        $('.postmenu-save-button-menu').on('click', function (event) {
            LP_Scope.saveShowMenu = !LP_Scope.saveShowMenu;
            var brother = $(event.currentTarget).next();
            if (LP_Scope.saveShowMenu) {
                brother.show();
                brother.on('mouseover', function () {
                    LP_Scope.overEditMenu = true;
                }).on('mouseleave', function () {
                    brother.hide();
                    LP_Scope.overEditMenu = true;
                    LP_Scope.saveShowMenu = false;
                });
            } else {
                brother.hide();
            }
        })
        $('#postmenu_duplicate_menu').on('click', function () {
            LP_Scope.duplicateMenu(LP_Scope.save_menu_button_links['menu_id'], null, function (data) {
                var msg = (data == 'error') ? LP_Scope.save_menu_button_links['repeitedItem'] : (!data) ? LP_Scope.save_menu_button_links['repeitedItem'] : postmenu_success_message,
                    success = (data && data != 'error');
                LP_Scope.notifyError(msg, success);
            })
        })
    }

    function addMenuLinkButton(buttonlabel) {
        //default pivot html
        var container = "#editable-post-name-full";
        //if permalink structure selected is plain then we need to change  the html container button
        // for a new html pivot change-permalinks


        //this variable is defined in class-postmenu-duplicate-menu.php, before render the
        //addMenuLinkButton function.
        if (typeof(permalinks_structure) !== "undefined" && permalinks_structure == "plain") {
            if (document.getElementById("change-permalinks") !== null) {
                container = "#change-permalinks";
            }
        }
        $(container).after('<span></span><button type="button"' +
            'id="postmenu-show-menu-link-form"' +
            'class="button button-small hide-if-no-js" aria-label="Edit permalink">' +
            buttonlabel + '</button></span>');
        $('#postmenu-show-menu-link-form').on('click', function () {
            var ele = this;
            $(ele).attr('disabled', 'disabled');
            LP_Scope.ableToOpen = false;
            $.post(ajaxurl,
                {'action': 'postmenu_menu_link_fieldsets', 'id': LP_Scope.current_postId, 'rownumber': '0'},
                function (data) {
                    LP_Scope.ableToOpen = true;
                    $('#edit-slug-box').after(data);
                });
            setTimeout(function () {
                if (!LP_Scope.ableToOpen) {
                    $(ele).removeAttr('disabled');
                }
            }, 5000);
            return false;
        })
    }

    function duplicateMenu(id, name, callback) {
        var data = {
            'action': 'postmenu_admin_ajax_duplicate_menu_link',
            'id': id
        }
        if (name) {
            data = $.extend(data, {'name': name});
        }
        $.post(ajaxurl,
            data,
            function (response) {
                if (callback) {
                    callback(response);
                }
            });
    }

    function notifyError(msg, success) {
        var messageBox = $('#message');
        if (messageBox.length > 0) {
            messageBox.find('p').text(msg);
        } else {
            $('.wrap h1:first').after('<div id="message" class="updated"><p>' + msg + '</p></div>');
        }
        var time = success ? 3000 : 7000;
        setTimeout(function () {
            if (window.location.search != window.location.search.replace('&duplicateerror=1', '')) {
                window.location.search = window.location.search.replace('&duplicateerror=1', '');
            } else {
                window.location.reload();
            }
        }, time);
    }

    function initEditMenuForm(params) {
        LP_Scope.selected_menu_object = params.selected_menu_object;
        LP_Scope.containerselector = params.containerselector;
        LP_Scope.success_delete = params.success_delete;
        LP_Scope.menuList = $('#menu-to-edit');
        LP_Scope.menuMaxDepth = params.menu_max_depth;
        LP_Scope.isRTL = !!( 'undefined' != typeof isRtl && isRtl );
        LP_Scope.negateIfRTL = ( 'undefined' != typeof isRtl && isRtl ) ? -1 : 1;
        LP_Scope.options = {
            menuItemDepthPerLevel: 30, // Do not use directly. Use depthToPx and pxToDepth instead.
            globalMaxDepth: 11,
            sortableItems: '> *',
            targetTolerance: 0
        };

        var create_menu_field = $('[name="postmenu_new_menu_name"]'),
            selected_menu_field = $('[name="selected-menu"]');

        create_menu_action = $('#create-new-menu-action'),
            add_to_menu_action = $('#add-to-menu-action'),
            select_menu_action = $('#select-menu-action'),
            update_menu_action = $('#update-menu-action');

        var create_new_menu_box = $('#create-new-menu');

        create_menu_field.on('keyup', function () {
            if ($(this).val()) {
                enableElement(create_menu_action);
            } else {
                disableElement(create_menu_action);
            }
        });
        create_menu_action.on('click', function () {
            LP_Scope.duplicateMenu(null, create_menu_field.val(), function (data) {
                if (data == 'error') {
                    alert('Menu name already exist');
                } else if (!data) {
                    alert('Error');
                } else {
                    selected_menu_field.append('<option value="' + data + '">' + create_menu_field.val() + '</option>');
                    enableElement(selected_menu_field);
                    enableElement(select_menu_action);
                    enableElement(add_to_menu_action);
                    enableElement(update_menu_action);
                    disableElement(create_menu_action);
                    create_menu_field.attr('value', '');
                    LP_Scope.changeSelectedMenu(data);
                }
            });
        });
        select_menu_action.on('click', function () {
            LP_Scope.changeSelectedMenu(selected_menu_field.val());
        });
        add_to_menu_action.on('click', function () {
            var item_meta = $('#nav-menu-meta'),
                form_data;

            form_data = {
                action: 'add-menu-item',
                menu: LP_Scope.selected_menu_object.id,
                'menu-settings-column-nonce': $('#menu-settings-column-nonce').val(),
                'menu-item': {
                    '-1': getNewMenuItemFormData(item_meta)
                }
            };
            $.post(ajaxurl, form_data, function (data) {
                printNewItem(data, null, null);
            });
        });
        //Click to save menu changes
        update_menu_action.on('click', function () {

            var locs = '',
                menuName = $('#menu-name'),
                menuNameVal = menuName.val();
            // Cancel and warn if invalid menu name
            /*if( !menuNameVal || menuNameVal == menuName.attr('title') || !menuNameVal.replace(/\s+/, '') ) {
             menuName.parent().addClass('form-invalid');
             return false;
             }*/
            // Copy menu theme locations
            $('#nav-menu-theme-locations select').each(function () {
                locs += '<input type="hidden" name="' + this.name + '" value="' + $(this).val() + '" />';
            });
            $('#update-nav-menu').append(locs);
            // Update menu item position data
            //liontude
            LP_Scope.menuList = $('#menu-to-edit');
            LP_Scope.targetList = LP_Scope.menuList;
            LP_Scope.menuList.find('.menu-item-data-position').val(function (index) {
                return index + 1;
            });
            var spiner = jQuery('.spinner.postmenu');
            spiner.css('visibility', 'visible');
            jQuery('.button.button-primary.save.alignright.postmenu').attr('disabled', 'disabled');
            $.post(ajaxurl, $('#postmenu_advanced_publish_form').serialize(), function (data) {
                if (data == '0') {
                    $(params.containerselector).remove();
                    if (LP_Scope.selector) {
                        $(LP_Scope.selector).show();
                        $('tr.hidden').remove();
                    }
                    if (params.rownumber < 1) {
                        enableElement($('#postmenu-show-menu-link-form'));
                    }
                    showSuccess(params.success_message);
                }
            });
            return false;
        });
        $('.button.cancel.postmenu').on('click', function () {
            $(params.containerselector).remove();
            if (LP_Scope.selector) {
                $(LP_Scope.selector).show();
                $('tr.hidden').remove();
            }
            if (params.rownumber < 1) {
                enableElement($('#postmenu-show-menu-link-form'));
            }
        });
        $('#show-create-new-menu').on('click', function () {
            if (LP_Scope.selected_menu_object.name) {
                selected_menu_field.prepend('<option value="-1">' + params.empty_menu_label + '</option>');
                LP_Scope.changeSelectedMenu(-1);
            }
            create_new_menu_box.slideDown();
        });
        LP_Scope.jQueryExtensions();
        LP_Scope.initSortables();
        postMenuLocationsEvents();
    }

    function changeSelectedMenu(menu_id) {
        var menu_name = '',
            selected_menu_field = $('[name="selected-menu"]'),
            create_new_menu_box = $('#create-new-menu'),
            select_menu_action = $('#select-menu-action'),
            add_to_menu_action = $('#add-to-menu-action'),
            selected_menu_box = $('#menu-management-liquid');

        selected_menu_field.find('option').each(function () {
            var option = $(this);
            if (option.val() == menu_id) {
                option.attr('selected', 'selected');
                menu_name = option.text();
            } else {
                option.removeAttr('selected');
            }
            if (option.val() == '-1' && menu_id != '-1') {
                option.remove();
            }
        });
        if (menu_id != -1) {
            if (menu_name && !LP_Scope.selected_menu_object.name) {
                enableElement(select_menu_action);
                enableElement(add_to_menu_action);
                selected_menu_box.slideDown();
            }
            create_new_menu_box.slideUp();
            LP_Scope.selected_menu_object.id = menu_id;
            LP_Scope.selected_menu_object.name = menu_name;
            LP_Scope.getMenuItemBox(LP_Scope.selected_menu_object.id);
            $('#menu').val(menu_id);
        }
    }

    function getMenuItemBox(menu) {
        var request = {
            'action': 'postmenu_admin_ajax_get_menu_items_box',
            'menu': menu
        };
        $.post(ajaxurl, request, function (data) {
            LP_Scope.menuList.html('');
            LP_Scope.menuList.append(data);
            LP_Scope.jQueryExtensions();
            LP_Scope.initSortables();
        });
        var request2 = {
            'action': 'postmenu_admin_ajax_get_menu_locations_box',
            'menu': menu
        };
        $.post(ajaxurl, request2, function (data) {
            $('#menu-location-box-container').html('');
            $('#menu-location-box-container').append(data);
            postMenuLocationsEvents();
        });
    }

    function initSortables() {
        var currentDepth = 0, originalDepth, minDepth, maxDepth,
            prev, next, prevBottom, nextThreshold, helperHeight, transport,
            menuEdge = LP_Scope.menuList.offset().left,
            body = $('body'), maxChildDepth,
            menuMaxDepth = LP_Scope.menuMaxDepth;

        postMenuItemsEvents();

        // Use the right edge if RTL.
        menuEdge += LP_Scope.isRTL ? LP_Scope.menuList.width() : 0;

        LP_Scope.menuList.sortable({
            handle: '.menu-item-handle',
            placeholder: 'sortable-placeholder',
            items: LP_Scope.options.sortableItems,
            start: function (e, ui) {
                var height, width, parent, children, tempHolder;

                // handle placement for rtl orientation
                if (LP_Scope.isRTL)
                    ui.item[0].style.right = 'auto';

                transport = ui.item.children('.menu-item-transport');

                // Set depths. currentDepth must be set before children are located.
                originalDepth = ui.item.menuItemDepth();
                updateCurrentDepth(ui, originalDepth);

                // Attach child elements to parent
                // Skip the placeholder
                parent = ( ui.item.next()[0] == ui.placeholder[0] ) ? ui.item.next() : ui.item;
                children = parent.childMenuItems();
                transport.append(children);

                // Update the height of the placeholder to match the moving item.
                height = transport.outerHeight();
                // If there are children, account for distance between top of children and parent
                height += ( height > 0 ) ? (ui.placeholder.css('margin-top').slice(0, -2) * 1) : 0;
                height += ui.helper.outerHeight();
                helperHeight = height;
                height -= 2; // Subtract 2 for borders
                ui.placeholder.height(height);

                // Update the width of the placeholder to match the moving item.
                maxChildDepth = originalDepth;
                children.each(function () {
                    var depth = $(this).menuItemDepth();
                    maxChildDepth = (depth > maxChildDepth) ? depth : maxChildDepth;
                });
                width = ui.helper.find('.menu-item-handle').outerWidth(); // Get original width
                width += LP_Scope.depthToPx(maxChildDepth - originalDepth); // Account for children
                width -= 2; // Subtract 2 for borders
                ui.placeholder.width(width);

                // Update the list of menu items.
                tempHolder = ui.placeholder.next('.menu-item');
                tempHolder.css('margin-top', helperHeight + 'px'); // Set the margin to absorb the placeholder
                ui.placeholder.detach(); // detach or jQuery UI will think the placeholder is a menu item
                $(this).sortable('refresh'); // The children aren't sortable. We should let jQ UI know.
                ui.item.after(ui.placeholder); // reattach the placeholder.
                tempHolder.css('margin-top', 0); // reset the margin

                // Now that the element is complete, we can update...
                updateSharedVars(ui);
            },
            stop: function (e, ui) {
                var children, subMenuTitle,
                    depthChange = currentDepth - originalDepth;

                // Return child elements to the list
                children = transport.children().insertAfter(ui.item);

                // Add "sub menu" description
                subMenuTitle = ui.item.find('.item-title .is-submenu');
                if (0 < currentDepth)
                    subMenuTitle.show();
                else
                    subMenuTitle.hide();

                // Update depth classes
                if (0 !== depthChange) {
                    ui.item.updateDepthClass(currentDepth);
                    children.shiftDepthClass(depthChange);
                    updateMenuMaxDepth(depthChange);
                }
                // Register a change
                // LP_Scope.registerChange();
                // Update the item data.
                ui.item.updateParentMenuItemDBId();

                // address sortable's incorrectly-calculated top in opera
                ui.item[0].style.top = 0;

                // handle drop placement for rtl orientation
                if (LP_Scope.isRTL) {
                    ui.item[0].style.left = 'auto';
                    ui.item[0].style.right = 0;
                }

                LP_Scope.refreshKeyboardAccessibility();
                LP_Scope.refreshAdvancedAccessibility();
            },
            change: function (e, ui) {
                // Make sure the placeholder is inside the menu.
                // Otherwise fix it, or we're in trouble.
                if (!ui.placeholder.parent().hasClass('menu'))
                    (prev.length) ? prev.after(ui.placeholder) : LP_Scope.menuList.prepend(ui.placeholder);

                updateSharedVars(ui);
            },
            sort: function (e, ui) {
                var offset = ui.helper.offset(),
                    edge = LP_Scope.isRTL ? offset.left + ui.helper.width() : offset.left,
                    depth = LP_Scope.negateIfRTL * LP_Scope.pxToDepth(edge - menuEdge);

                // Check and correct if depth is not within range.
                // Also, if the dragged element is dragged upwards over
                // an item, shift the placeholder to a child position.
                if (depth > maxDepth || offset.top < ( prevBottom - LP_Scope.options.targetTolerance )) {
                    depth = maxDepth;
                } else if (depth < minDepth) {
                    depth = minDepth;
                }

                if (depth != currentDepth)
                    updateCurrentDepth(ui, depth);

                // If we overlap the next element, manually shift downwards
                if (nextThreshold && offset.top + helperHeight > nextThreshold) {
                    next.after(ui.placeholder);
                    updateSharedVars(ui);
                    $(this).sortable('refreshPositions');
                }
            }
        });

        function updateSharedVars(ui) {
            var depth;

            prev = ui.placeholder.prev('.menu-item');
            next = ui.placeholder.next('.menu-item');

            // Make sure we don't select the moving item.
            if (prev[0] == ui.item[0]) prev = prev.prev('.menu-item');
            if (next[0] == ui.item[0]) next = next.next('.menu-item');

            prevBottom = (prev.length) ? prev.offset().top + prev.height() : 0;
            nextThreshold = (next.length) ? next.offset().top + next.height() / 3 : 0;
            minDepth = (next.length) ? next.menuItemDepth() : 0;

            if (prev.length)
                maxDepth = ( (depth = prev.menuItemDepth() + 1) > LP_Scope.options.globalMaxDepth ) ? LP_Scope.options.globalMaxDepth : depth;
            else
                maxDepth = 0;
        }

        function updateCurrentDepth(ui, depth) {
            ui.placeholder.updateDepthClass(depth, currentDepth);
            currentDepth = depth;
        }

        function updateMenuMaxDepth(depthChange) {
            var depth, newDepth = menuMaxDepth;
            if (depthChange === 0) {
                return;
            } else if (depthChange > 0) {
                depth = maxChildDepth + depthChange;
                if (depth > menuMaxDepth)
                    newDepth = depth;
            } else if (depthChange < 0 && maxChildDepth == menuMaxDepth) {
                while (!$('.menu-item-depth-' + newDepth, LP_Scope.menuList).length && newDepth > 0)
                    newDepth--;
            }
            // Update the depth class.
            LP_Scope.menuMaxDepth = newDepth;
            menuMaxDepth = newDepth;
        }
    }

    function jQueryExtensions() {
        // jQuery extensions
        $.fn.extend({
            menuItemDepth: function () {
                var margin = LP_Scope.isRTL ? this.eq(0).css('margin-right') : this.eq(0).css('margin-left');
                return LP_Scope.pxToDepth(margin && -1 != margin.indexOf('px') ? margin.slice(0, -2) : 0);
            },
            updateDepthClass: function (current, prev) {
                return this.each(function () {
                    var t = $(this);
                    prev = prev || t.menuItemDepth();
                    $(this).removeClass('menu-item-depth-' + prev)
                        .addClass('menu-item-depth-' + current);
                });
            },
            shiftDepthClass: function (change) {
                return this.each(function () {
                    var t = $(this),
                        depth = t.menuItemDepth(),
                        newDepth = depth + change;

                    t.removeClass('menu-item-depth-' + depth)
                        .addClass('menu-item-depth-' + ( newDepth ));

                    if (0 === newDepth) {
                        t.find('.is-submenu').hide();
                    }
                });
            },
            childMenuItems: function () {
                var result = $();
                this.each(function () {
                    var t = $(this), depth = t.menuItemDepth(), next = t.next('.menu-item');
                    while (next.length && next.menuItemDepth() > depth) {
                        result = result.add(next);
                        next = next.next('.menu-item');
                    }
                });
                return result;
            },
            shiftHorizontally: function (dir) {
                return this.each(function () {
                    var t = $(this),
                        depth = t.menuItemDepth(),
                        newDepth = depth + dir;

                    // Change .menu-item-depth-n class
                    t.moveHorizontally(newDepth, depth);
                });
            },
            moveHorizontally: function (newDepth, depth) {
                return this.each(function () {
                    var t = $(this),
                        children = t.childMenuItems(),
                        diff = newDepth - depth,
                        subItemText = t.find('.is-submenu');

                    // Change .menu-item-depth-n class
                    t.updateDepthClass(newDepth, depth).updateParentMenuItemDBId();

                    // If it has children, move those too
                    if (children) {
                        children.each(function () {
                            var t = $(this),
                                thisDepth = t.menuItemDepth(),
                                newDepth = thisDepth + diff;
                            t.updateDepthClass(newDepth, thisDepth).updateParentMenuItemDBId();
                        });
                    }

                    // Show "Sub item" helper text
                    if (0 === newDepth)
                        subItemText.hide();
                    else
                        subItemText.show();
                });
            },
            updateParentMenuItemDBId: function () {
                return this.each(function () {
                    var item = $(this),
                        input = item.find('.menu-item-data-parent-id'),
                        depth = parseInt(item.menuItemDepth(), 10),
                        parentDepth = depth - 1,
                        parent = item.prevAll('.menu-item-depth-' + parentDepth).first();

                    if (0 === depth) { // Item is on the top level, has no parent
                        input.val(0);
                    } else { // Find the parent item, and retrieve its object id.
                        input.val(parent.find('.menu-item-data-db-id').val());
                    }
                });
            },
            hideAdvancedMenuItemFields: function () {
                return this.each(function () {
                    var that = $(this);
                    $('.hide-column-tog').not(':checked').each(function () {
                        that.find('.field-' + $(this).val()).addClass('hidden-field');
                    });
                });
            },
            /**
             * Adds selected menu items to the menu.
             *
             * @param jQuery metabox The metabox jQuery object.
             */
            addSelectedToMenu: function (processMethod) {
                if (0 === $('#menu-to-edit').length) {
                    return false;
                }

                return this.each(function () {
                    var t = $(this), menuItems = {},
                        checkboxes = ( menus.oneThemeLocationNoMenus && 0 === t.find('.tabs-panel-active .categorychecklist li input:checked').length ) ? t.find('#page-all li input[type="checkbox"]') : t.find('.tabs-panel-active .categorychecklist li input:checked'),
                        re = /menu-item\[([^\]]*)/;

                    processMethod = processMethod || LP_Scope.addMenuItemToBottom;

                    // If no items are checked, bail.
                    if (!checkboxes.length)
                        return false;

                    // Show the ajax spinner
                    t.find('.button-controls .spinner').addClass('is-active');

                    // Retrieve menu item data
                    $(checkboxes).each(function () {
                        var t = $(this),
                            listItemDBIDMatch = re.exec(t.attr('name')),
                            listItemDBID = 'undefined' == typeof listItemDBIDMatch[1] ? 0 : parseInt(listItemDBIDMatch[1], 10);

                        if (this.className && -1 != this.className.indexOf('add-to-top'))
                            processMethod = LP_Scope.addMenuItemToTop;
                        menuItems[listItemDBID] = t.closest('li').getItemData('add-menu-item', listItemDBID);
                    });

                    // Add the items
                    LP_Scope.addItemToMenu(menuItems, processMethod, function () {
                        // Deselect the items and hide the ajax spinner
                        checkboxes.removeAttr('checked');
                        t.find('.button-controls .spinner').removeClass('is-active');
                    });
                });
            },
            getItemData: function (itemType, id) {
                itemType = itemType || 'menu-item';

                var itemData = {}, i,
                    fields = [
                        'menu-item-db-id',
                        'menu-item-object-id',
                        'menu-item-object',
                        'menu-item-parent-id',
                        'menu-item-position',
                        'menu-item-type',
                        'menu-item-title',
                        'menu-item-url',
                        'menu-item-description',
                        'menu-item-attr-title',
                        'menu-item-target',
                        'menu-item-classes',
                        'menu-item-xfn'
                    ];

                if (!id && itemType == 'menu-item') {
                    id = this.find('.menu-item-data-db-id').val();
                }

                if (!id) return itemData;

                this.find('input').each(function () {
                    var field;
                    i = fields.length;
                    while (i--) {
                        if (itemType == 'menu-item')
                            field = fields[i] + '[' + id + ']';
                        else if (itemType == 'add-menu-item')
                            field = 'menu-item[' + id + '][' + fields[i] + ']';

                        if (
                            this.name &&
                            field == this.name
                        ) {
                            itemData[fields[i]] = this.value;
                        }
                    }
                });

                return itemData;
            },
            setItemData: function (itemData, itemType, id) { // Can take a type, such as 'menu-item', or an id.
                itemType = itemType || 'menu-item';

                if (!id && itemType == 'menu-item') {
                    id = $('.menu-item-data-db-id', this).val();
                }

                if (!id) return this;

                this.find('input').each(function () {
                    var t = $(this), field;
                    $.each(itemData, function (attr, val) {
                        if (itemType == 'menu-item')
                            field = attr + '[' + id + ']';
                        else if (itemType == 'add-menu-item')
                            field = 'menu-item[' + id + '][' + attr + ']';

                        if (field == t.attr('name')) {
                            t.val(val);
                        }
                    });
                });
                return this;
            }
        });
    }

    function pxToDepth(px) {
        return Math.floor(px / LP_Scope.options.menuItemDepthPerLevel);
    }

    function depthToPx(depth) {
        return depth * LP_Scope.options.menuItemDepthPerLevel;
    }

    function refreshAdvancedAccessibilityOfItem(itemToRefresh) {

        // Only refresh accessibility when necessary
        if (true !== $(itemToRefresh).data('needs_accessibility_refresh')) {
            return;
        }

        var thisLink, thisLinkText, primaryItems, itemPosition, title,
            parentItem, parentItemId, parentItemName, subItems,
            $this = $(itemToRefresh),
            menuItem = $this.closest('li.menu-item').first(),
            depth = menuItem.menuItemDepth(),
            isPrimaryMenuItem = ( 0 === depth ),
            itemName = $this.closest('.menu-item-handle').find('.menu-item-title').text(),
            position = parseInt(menuItem.index(), 10),
            prevItemDepth = ( isPrimaryMenuItem ) ? depth : parseInt(depth - 1, 10),
            prevItemNameLeft = menuItem.prevAll('.menu-item-depth-' + prevItemDepth).first().find('.menu-item-title').text(),
            prevItemNameRight = menuItem.prevAll('.menu-item-depth-' + depth).first().find('.menu-item-title').text(),
            totalMenuItems = $('#menu-to-edit li').length,
            hasSameDepthSibling = menuItem.nextAll('.menu-item-depth-' + depth).length;

        menuItem.find('.field-move').toggle(totalMenuItems > 1);

        // Where can they move this menu item?
        if (0 !== position) {
            thisLink = menuItem.find('.menus-move-up');
            thisLink.attr('aria-label', menus.moveUp).css('display', 'inline');
        }

        if (0 !== position && isPrimaryMenuItem) {
            thisLink = menuItem.find('.menus-move-top');
            thisLink.attr('aria-label', menus.moveToTop).css('display', 'inline');
        }

        if (position + 1 !== totalMenuItems && 0 !== position) {
            thisLink = menuItem.find('.menus-move-down');
            thisLink.attr('aria-label', menus.moveDown).css('display', 'inline');
        }

        if (0 === position && 0 !== hasSameDepthSibling) {
            thisLink = menuItem.find('.menus-move-down');
            thisLink.attr('aria-label', menus.moveDown).css('display', 'inline');
        }

        if (!isPrimaryMenuItem) {
            thisLink = menuItem.find('.menus-move-left'),
                thisLinkText = menus.outFrom.replace('%s', prevItemNameLeft);
            thisLink.attr('aria-label', menus.moveOutFrom.replace('%s', prevItemNameLeft)).text(thisLinkText).css('display', 'inline');
        }

        if (0 !== position) {
            if (menuItem.find('.menu-item-data-parent-id').val() !== menuItem.prev().find('.menu-item-data-db-id').val()) {
                thisLink = menuItem.find('.menus-move-right'),
                    thisLinkText = menus.under.replace('%s', prevItemNameRight);
                thisLink.attr('aria-label', menus.moveUnder.replace('%s', prevItemNameRight)).text(thisLinkText).css('display', 'inline');
            }
        }

        if (isPrimaryMenuItem) {
            primaryItems = $('.menu-item-depth-0'),
                itemPosition = primaryItems.index(menuItem) + 1,
                totalMenuItems = primaryItems.length,

                // String together help text for primary menu items
                title = menus.menuFocus.replace('%1$s', itemName).replace('%2$d', itemPosition).replace('%3$d', totalMenuItems);
        } else {
            parentItem = menuItem.prevAll('.menu-item-depth-' + parseInt(depth - 1, 10)).first(),
                parentItemId = parentItem.find('.menu-item-data-db-id').val(),
                parentItemName = parentItem.find('.menu-item-title').text(),
                subItems = $('.menu-item .menu-item-data-parent-id[value="' + parentItemId + '"]'),
                itemPosition = $(subItems.parents('.menu-item').get().reverse()).index(menuItem) + 1;

            // String together help text for sub menu items
            title = menus.subMenuFocus.replace('%1$s', itemName).replace('%2$d', itemPosition).replace('%3$s', parentItemName);
        }

        // @todo Consider to update just the `aria-label` attribute.
        $this.attr('aria-label', title).text(title);

        // Mark this item's accessibility as refreshed
        $this.data('needs_accessibility_refresh', false);
    }

    function refreshAdvancedAccessibility() {

        // Hide all the move buttons by default.
        $('.menu-item-settings .field-move .menus-move').hide();

        // Mark all menu items as unprocessed
        $('a.item-edit').data('needs_accessibility_refresh', true);

        // All open items have to be refreshed or they will show no links
        $('.menu-item-edit-active a.item-edit').each(function () {
            LP_Scope.refreshAdvancedAccessibilityOfItem(this);
        });
    }

    function refreshKeyboardAccessibility() {
        $('a.item-edit').off('focus').on('focus', function () {
            $(this).off('keydown').on('keydown', function (e) {

                var arrows,
                    $this = $(this),
                    thisItem = $this.parents('li.menu-item'),
                    thisItemData = thisItem.getItemData();

                // Bail if it's not an arrow key
                if (37 != e.which && 38 != e.which && 39 != e.which && 40 != e.which)
                    return;

                // Avoid multiple keydown events
                $this.off('keydown');

                // Bail if there is only one menu item
                if (1 === $('#menu-to-edit li').length)
                    return;

                // If RTL, swap left/right arrows
                arrows = {'38': 'up', '40': 'down', '37': 'left', '39': 'right'};
                if ($('body').hasClass('rtl'))
                    arrows = {'38': 'up', '40': 'down', '39': 'left', '37': 'right'};

                switch (arrows[e.which]) {
                    case 'up':
                        LP_Scope.moveMenuItem($this, 'up');
                        break;
                    case 'down':
                        LP_Scope.moveMenuItem($this, 'down');
                        break;
                    case 'left':
                        LP_Scope.moveMenuItem($this, 'left');
                        break;
                    case 'right':
                        LP_Scope.moveMenuItem($this, 'right');
                        break;
                }
                // Put focus back on same menu item
                $('#edit-' + thisItemData['menu-item-db-id']).focus();
                return false;
            });
        });
    }

    function showSuccess(message) {
        $("hr.wp-header-end").after('<div id="message" class="updated notice is-dismissible">' +
            '<p>' + message + '</p>' +
            '<button type="button" class="notice-dismiss">' +
            '<span class="screen-reader-text">Dismiss this notice.</span>' +
            '</button>' +
            '</div>');

        setTimeout(function () {
            $("#message").remove();
        }, 5000);
        $(document).on("click", ".notice-dismiss", function () {
            $("#message").remove();
        });
    }

    function postMenuItemsEvents() {
        disableHref($('.item-controls a.item-edit'));
        disableHref($('.item-cancel'));

        //this code is executed only when the menu-item was created and saved
        /*$('.item-delete').on('click', function (e) {
         e.preventDefault();

         var item = this.id.split('-')[1],
         req = {
         'action': 'postmenu_admin_ajax_delete_menu_item',
         'menu-item': item
         };
         $.post(ajaxurl, req, function () {
         showSuccess(LP_Scope.success_delete);
         getMenuItemBox(LP_Scope.selected_menu_object.id);
         });
         });*/
    }

    //This method make a request to delete a menu item
    //I could remove the duplicated, but it  will be in a diferent time
    function postMenuItemDelete(parameters) {
        $.post(ajaxurl, parameters, function () {
            showSuccess(LP_Scope.success_delete);
            getMenuItemBox(LP_Scope.selected_menu_object.id);
        });
    }

    function postMenuLocationsEvents() {
        $('#delete-menu-action').on('click', function () {
            var param = {
                'action': 'postmenu_admin_ajax_delete_menu',
                'menu': LP_Scope.selected_menu_object.id
            }
            $.post(ajaxurl, param, function () {
                var options = $('[name="selected-menu"]').find('option'),
                    selected_menu_field = $('[name="selected-menu"]'),
                    update_menu_action = $('#update-menu-action'),
                    select_menu_action = $('#select-menu-action'),
                    add_to_menu_action = $('#add-to-menu-action'),
                    selected_menu_box = $('#menu-management-liquid');

                function clearMenus() {
                    disableElement(select_menu_action);
                    disableElement(add_to_menu_action);
                    disableElement(selected_menu_field);
                    disableElement(update_menu_action);
                    selected_menu_box.slideUp();
                    LP_Scope.selected_menu_object = {name: '', id: ''};
                }

                if (options.length > 1) {
                    var i = 0,
                        pos = -1;
                    while (i < options.length && options[i].value == LP_Scope.selected_menu_object.id) {
                        pos = i;
                        i++;
                    }
                    if (pos >= 0) {
                        $(options[pos]).remove();
                    }
                    if (i != pos) {
                        LP_Scope.changeSelectedMenu(options[i].value);
                    } else {
                        clearMenus();
                    }
                } else {
                    $(options[0]).remove();
                    selected_menu_field.css('min-width', '120px');
                    clearMenus();
                }
                showSuccess(LP_Scope.success_delete);
            });
        });
    }

    $(document).on('click', '.postmenu-arrow', function () {
        var menu_id = this.id.split('-')[1],
            slide = $('#' + this.id),
            content = $('#postmenu_field_container-' + menu_id);
        if (slide.hasClass('up')) {
            content.slideUp();
        } else {
            content.slideDown();
        }
        slide.toggleClass('up');

        return false
    });

    $(document).on('click', '.postmenu-user-type', function () {
        var menu_id = this.closest('.postmenu-advanced-menu-item-fields').id.split('-')[1];
        if (this.value == 'in') {
            $('#postmenu_roles_container-' + menu_id).slideDown();
        } else {
            $('#postmenu_roles_container-' + menu_id).slideUp();
        }
    });

    $(document).on('click', '.postmenu-duplicate-menu-item', function (event) {
        event.preventDefault();
        var li = $(this).closest('li.menu-item'),
            depth = 'menu-item-depth-' + getDepth(li),
            form_data;

        form_data = {
            action: 'add-menu-item',
            menu: $('#menu').val(),
            'menu-settings-column-nonce': $('#menu-settings-column-nonce').val(),
            'menu-item': {
                '-1': getNewMenuItemFormData(li)
            }
        };

        $.post(ajaxurl, form_data, function (data) {
            printNewItem(data, li, depth);
        });
    });

    //Listen click event on the document, it used when we added the menu link,
    //but you don't saved the changes
    $(document).on('click', '.item-delete', function (event) {
        event.preventDefault();
        var item = this.id.split('-')[1],
            req = {
                'action': 'postmenu_admin_ajax_delete_menu_item',
                'menu-item': item
            };
        postMenuItemDelete(req);
        return false;
    });
    $(document).on('click', '.item-cancel', function (event) {
        event.preventDefault();
        var settings = $(event.target).closest('.menu-item-settings'),
            thisMenuItem = $(event.target).closest('.menu-item');
            thisMenuItem.removeClass('menu-item-edit-active').addClass('menu-item-edit-inactive');
        return false;
    });
    $(document).on('keypress', function (event) {
        if (event.which !== 13) {
            return true;
        }
        var target = $(event.target);
        if (target.is("input")) {
            if (target.attr('name') == "postmenu_new_menu_name" && target.val().length > 0) {
                create_menu_action.trigger('click');
            }
        }
    });


    function disableElement(element) {
        element.attr('disabled', 'disabled');
    }

    function enableElement(element) {
        element.removeAttr('disabled');
    }

    function getDepth(element) {
        return element.get(0).className.split('menu-item-depth-')[1].split(' ')[0];
    }

    function printNewItem(itemMarkUp, originItem, depth) {
        var newItem = $(itemMarkUp);

        $('.hide-column-tog').not(':checked').each(function () {
            newItem.find('.field-' + $(this).val()).addClass('hidden-field');
        });

        if (originItem) {
            newItem.removeClass('menu-item-depth-0');
            newItem.addClass(depth);

            newItem = newItem.wrap('<div>').parent().html();

            if (originItem.next().hasClass(depth) || originItem.parent().children('li').last().get(0) === originItem.get(0)) {
                originItem.after(newItem);
            } else if (getDepth(originItem.next()) < getDepth(originItem)) {
                originItem.after(newItem);
            } else {
                if (getDepth(originItem) != 0) {
                    depth = 'menu-item-depth-' + ( getDepth(originItem) - 1 );
                }
                originItem.nextUntil('.' + depth).last().after(newItem);
            }
        } else {
            newItem = newItem.wrap('<div>').parent().html();
            LP_Scope.menuList.append(newItem);
        }

        //thow an error when remove the link menu and it added again
        //check why this is happen and how fix it
        if (LP_Scope.selected_menu_object && LP_Scope.selected_menu_object.id) {
            var dropdownLink = $(newItem).find('.item-controls a.item-edit');
            disableHref($('#' + dropdownLink[0].id));
        }
    }

    function disableHref(element) {
        element.on('click', function (e) {
            e.preventDefault();
            var menu_item = $(this).closest('.menu-item');
            $('.menu-item').each(function () {
                if (this.id == menu_item[0].id && !$(this).hasClass('menu-item-edit-active')) {
                    $(menu_item).removeClass('menu-item-edit-inactive');
                    $(menu_item).addClass('menu-item-edit-active')
                } else {
                    $(this).removeClass('menu-item-edit-active');
                    $(this).addClass('menu-item-edit-inactive');
                }
            });
        });
    }

    function getNewMenuItemFormData(containerForm) {
        function validateNullableField(selector) {
            var field = containerForm.find(selector);
            return (field.length > 0) ? field.val() : '';
        }

        return {
            'menu-item-db-id': 0,
            'menu-item-object-id': validateNullableField('input.menu-item-data-object-id'),
            'menu-item-object': validateNullableField('input.menu-item-data-object'),
            'menu-item-parent-id': validateNullableField('input.menu-item-data-parent-id'),
            'menu-item-type': validateNullableField('input.menu-item-data-type'),
            'menu-item-title': validateNullableField('input.edit-menu-item-title'),
            'menu-item-url': validateNullableField('input.edit-menu-item-url'),
            'menu-item-description': validateNullableField('textarea.edit-menu-item-description'),
            'menu-item-attr-title': validateNullableField('input.edit-menu-item-attr-title'),
            'menu-item-target': validateNullableField('.field-link-target input[type=checkbox]'),
            'menu-item-classes': validateNullableField('input.edit-menu-item-classes'),
            'menu-item-xfn': validateNullableField('input.edit-menu-item-xfn')
        }
    }

    /**
     * End duplicate menu functions
     * Start settings functions
     */

    $(document).on('click', '.postmenu-nav-tab-wrapper a', function () {
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('section').hide().eq($(this).index()).show();
        return false;
    });

})(jQuery);
