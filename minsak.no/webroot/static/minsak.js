/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.
*/


var minsak = {};
(function($) {
    minsak = {
            
        locations: null,
        language: 'nb',
        
        init: function() {
            if(!jQuery.browser.msie || jQuery.browser.version > 6) {
                this.moveLightboxDOM();
            }
        },
        
        moveLightboxDOM: function() {
        	$('body').append($('.lightbox'));
        },

        sendEmail: function(validationResult) {
            var form = $('#send-email-form');
            var to = $('#to').val();
            var from = $('#from').val();
            var message = $('#message').val();
            return false;
        },
        
        initUserAccessForm: function() {
            $('.location-wrapper').each(function(i, e) {
                var element = $(e);
                var classes = element.attr('class').split(' ');
                var locationId = 0;
                for (i = 0; i < classes.length; i++) {
                    if (/^location_\d+$/.test(classes[i])) {
                        locationId = classes[i].substr(9);
                        break;
                    }
                }
                if (locationId > 0) {
                    var active = element.children('.location-elements-active');
                    var inactive = element.children('.location-elements-inactive');
                    var deleteRoleButton = element.find('.delete-role');
                    var addRoleButton = element.find('.add-role');
                    var deleteInput = element.find('[name="' + locationId + '_del"]');
                    var addInput = element.find('[name="' + locationId + '_add"]');
                    deleteRoleButton.click(function() {
                        active.addClass('hide');
                        inactive.removeClass('hide');
                        deleteInput.val('1');
                        addInput.val('');
                        return false;
                    });
                    addRoleButton.click(function() {
                        inactive.addClass('hide');
                        active.removeClass('hide');
                        deleteInput.val('');
                        addInput.val('1');
                        return false;
                    });
                } else {
                    //console.log("no location id for " + classes);
                }
            });
        },
        
        initInitiativeForm: function(initiativeId) {
            var that = this;
            var form = $('#edit-initiative-form');
            
            $('.calendar').show();
            
            new AjaxUpload('#initiative-upload-image-button', {
                action: '/ajax/saveImage',
                name: 'image',
                data: {
                    'initiativeId': initiativeId
                },
                onSubmit: function(file, ext) {
                    if (ext && /^(jpg|jpeg|gif|png)$/.test(ext)) {
                        $('#initiative-upload-image-status').text('Overfører...');
                    } else {
                        $('#initiative-upload-image-status').text('Bare bilder er tillatt');
                        return false;
                    }
                },
                onComplete: function(file, response) {
                    var data = $.parseJSON(response);
                    if (typeof(data) == 'object' && data.hasOwnProperty('imageUrl')) {
                        $('#initiative-upload-image-status').text('Overført ' + file);
                        var imageUrl = data.imageUrl;
                        $('#image-container img').attr('src', imageUrl);
                        $('#image-container').show();
                        $('#imageupload-container').hide();
                    } else {
                        $('#initiative-upload-image-status').text('Feil ved overføring');
                    }
                    //TODO: force reload of the image...
                    //loadImages($pageId);
                }
            });
            
            that.loadFlickrSearchForm(initiativeId);
            $('#flickr-search-form input[type="submit"]').click(function() {
                that.loadFlickrSearchForm(initiativeId);
                return false;
            });
            
            $('#remove-image-button').click(function() {
                $('#image-container').hide();
                $('#imageupload-container').show();
                $('#image-container img').attr('src', '');
                $.ajax('/ajax/deleteImage', {data: {initiativeId: initiativeId}});
                return false;
            });
            
        },
        
        loadFlickrSearchForm: function(initiativeId) {
            var that = this;
            var searchTerm = $('#flickr-search-form input[type="text"]').val();
            $.when($.ajax('/ajax/searchFlickr', {data: {search: searchTerm}, dataType:'json'})).done(
                function(data) {
                    if (typeof(data) != 'object') {
                        // nothing returned ???
                    } else if (data.hasOwnProperty('error')) {
                        // caused by exception in curl call
                    } else if (!data.hasOwnProperty('stat') || data.stat != 'ok' || !data.hasOwnProperty('photos') || !data.photos.hasOwnProperty('photo')) {
                        // flickr error
                    } else {
                        var photos = data.photos.photo;
                        var pager = $('#flickr-paging');
                        var currentPage = 0;
                        pager.data('currentPage', currentPage);
                        var numPages = Math.ceil(photos.length / 15);
                        that.updateFlickrImageList(photos, initiativeId);
                        pager.children('.note').text('(viser ' + (1 + currentPage * 15) + '-' + Math.min(photos.length, (15 + currentPage * 15)) + ' av ' + photos.length + ')');
                        pager.children('.next').click(function() {
                            var currentPage = pager.data('currentPage');
                            currentPage = Math.min(currentPage + 1, numPages - 1);
                            pager.data('currentPage', currentPage);
                            that.updateFlickrImageList(photos, initiativeId);
                            return false;
                        });
                        pager.children('.prev').click(function() {
                            var currentPage = pager.data('currentPage');
                            currentPage = Math.max(currentPage - 1, 0);
                            pager.data('currentPage', currentPage);
                            that.updateFlickrImageList(photos, initiativeId);
                            return false;
                        });
                    }
                }
            );
        },
        
        updateFlickrImageList: function(photos, initiativeId) {
            var that = this;

            var list = $('#flickr-image-list');
            var pager = $('#flickr-paging');
            var prev = pager.children('.prev');
            var next = pager.children('.next');
            var pageList = pager.children('ul');
            var note = pager.children('.note');
            var numPages = Math.ceil(photos.length / 15);
            
            var currentPage = pager.data('currentPage');
            list.empty();
            for (var i = currentPage * 15; i < Math.min(photos.length, 15 + currentPage * 15); i++) {
                var src = photos[i].url_t.replace('http:', 'https:');
                var li = $('<li><a href="#"><img src="' + src + '" width="92" height="62" alt="image description" /></a></li>')
                li.children('a').data('index', i);
                li.data('flickr_id', photos[i].id);
                list.append(li);
            }
            var close = $('#flickr-popup .close');
            list.find('li > a').click(function() {
                close.click();
                var index = $(this).data('index');
                $('#image-container').show();
                $('#imageupload-container').hide();
                $('#image-container img').attr('src', photos[index].url_t);
                var photographer = photos[index].title + " / " + photos[index].ownername;
                $('#photographer').val(photographer);
                $.when($.ajax('/ajax/addFlickrImage', {data: {initiativeId: initiativeId, flickrImageId: photos[index].id, flickrImageCredits: photographer }, dataType:'json'})).done(
                    function(result) {
                        //console.log(result);
                    }
                );
                return false;
            });
            pageList.empty();
            pageList.append(this.createFlickrPagerLi(0, currentPage));
            if (currentPage > 4) {
                pageList.append($('<li>...</li>'));
            }
            for (var page = Math.max(0, currentPage - 3); page <= Math.min(numPages - 1, currentPage + 3); page++) {
                if (page > 0 && page < numPages - 1) {
                    pageList.append(this.createFlickrPagerLi(page, currentPage));
                }
            }
            if (currentPage < numPages - 5) {
                pageList.append($('<li>...</li>'));
            }
            if (numPages > 1) {
                pageList.append(this.createFlickrPagerLi(numPages - 1, currentPage));
            }
            pageList.children().click(function() {
                pager.data('currentPage', $(this).data('page'));
                that.updateFlickrImageList(photos, initiativeId);
            });
            if (numPages > 1) {
                prev.show();
                next.show();
            } else {
                prev.hide();
                next.hide();
            }
        },
        
        createFlickrPagerLi: function(page, current) {
            var li = $('<li/>').data('page', page);
            if (page != current) {
                var a = $('<a/>')
                li.append($('<a/>').append(page + 1));
            } else {
                li.append(page + 1);
            }
            return li;
        },

        
        findLocationFormInit: function(locationSelectorId, defaultLocationId, mode, buttonId) {
            var locationSelector = $(locationSelectorId);
            locationSelector.text('Laster data...');
            var form;
            if (mode == 0) {
                form = $('<form class="select-form" onsubmit="return false;"><fieldset><span class="txt"><input tabindex="12" type="text" title="Søk kommune/fylke" value=""/></span><input tabindex="13" class="submit" type="submit" value="Velg" /><div class="autocomplete"><div class="holder"><ul></ul></div></div></fieldset></form>');
            } else {
                form = $('<div class="select-form"><span class="txt"><input type="hidden" id="locationId" name="locationId" value="0"/><input tabindex="12" type="text" title="Søk kommune/fylke" value=""/></span><div class="autocomplete"><div class="holder"><ul></ul></div></div></div>');
            }
            var list = form.find('ul');
            var popup = form.find('.autocomplete');
            var input = form.find('input[type="text"]');
            var button = form.find('input[type="submit"]');
            var locationInput = form.find('input[type="hidden"]');
            input.data('currentIndex', -1);
            input.data('defaultLocationId', defaultLocationId);
            input.keypress(function(event) {
                return event.keyCode != 13;
            });
            var that = this;
            $.when($.ajax('/ajax/locations', {dataType:'json'})).done(
                function(ajaxLocations) {
                    that.locations = ajaxLocations;
                    locationSelector.empty();
                    locationSelector.append(form);
                    if (defaultLocationId) {
                        input.val(that.locations[defaultLocationId].name);
                        locationInput.val(that.locations[defaultLocationId].id);
                    }
                    input.keyup(function(e) {
                        that.findLocationKeyup(e, input, locationInput, popup, list, mode);
                    });
                    input.blur(function() {
                    	setTimeout(function() {
                    	    defaultLocationId = input.data('defaultLocationId');
                    	    if (mode != 0 && input.val() == '') {
                    	        locationInput.val(0);
                    	    } else if (defaultLocationId > 0) {
                    	        input.val(that.locations[defaultLocationId].name);
                    	        locationInput.val(that.locations[defaultLocationId].id);
                    	    } else {
                    	        input.val('');
                    	        locationInput.val(0);
                    	    }
                    		input.data('dataId', -1);
                    		popup.hide();
                    	}, 200); // Delay required for <a> in children to work
                    });
                    if (button.length == 0 && buttonId != undefined) {
                        button = $(buttonId);
                    }
                    if (button.length > 0) {
                        button.click(function() {
                            if (popup.is(':visible')) {
                                var children = list.children();
                                var selected = list.find('.selected');
                                var element = null;
                                if (children.length == 1) {
                                    element = children.first();
                                } else if (selected.length == 1) {
                                    element = selected;
                                }
                                if (element != null) {
                                    var locationId = element.data('dataId');
                                    if (mode == 0) {
                                        location.href = that.locations[locationId].slug;
                                    } else {
                                        input.val(that.locations[locationId].name);
                                        locationInput.val(that.locations[locationId].id);
                                        input.data('defaultLocationId', locationId);
                                        return false;
                                    }
                                }
                            }
                        });
                    }
                }
            );
        },
        
        findLocationKeyup: function(e, input, locationInput, popup, list, mode) {
            var that = this;
            var key = e.keyCode;
            var currentIndex = input.data('currentIndex');
            var defaultLocationId = input.data('defaultLocationId');
            var items = list.children();
            var selected = null;
            var selectedId = -1;
            if (currentIndex >= 0) {
                selected = items.eq(currentIndex);
                selectedId = selected.data('dataId');
            }
            switch (key) {
            case 38: // up
                if (items.length > 0) {
                    if (currentIndex <= 0) {
                        currentIndex = items.length - 1;
                    } else {
                        currentIndex--;
                    }
                    var selected = items.eq(currentIndex);
                    selected.siblings().removeClass('selected');
                    selected.addClass('selected');
                }
                break;
            case 40: // down
                if (items.length > 0) {
                    if (currentIndex < 0 || currentIndex == items.length - 1) {
                        currentIndex = 0;
                    } else {
                        currentIndex++;
                    }
                    var selected = items.eq(currentIndex);
                    selected.siblings().removeClass('selected');
                    selected.addClass('selected');
                }
                break;
            case 27: // esc
                popup.hide();
                if (mode != 0 && input.val() == '') {
                    locationInput.val(0);
                } else if (defaultLocationId > 0) {
                    input.val(this.locations[defaultLocationId].name);
                    locationInput.val(this.locations[defaultLocationId].id);
                } else {
                    input.val('');
                    locationInput.val(0);
                }
                currentIndex = -1;
                break;
            case 13: // enter
                if (mode != 0 && input.val() == '') {
                    locationInput.val(0);
                    popup.hide();
                    currentIndex = -1;
                    defaultLocationId = 0;
                } else if (selectedId >= 0 || items.length == 1) {
                	console.log('selectedId = ' + selectedId);
                	var loc;
                	if (selectedId >= 0) {
                		loc = this.locations[selectedId];
                	} else {
                		loc = this.locations[items.eq(0).data('dataId')];
                	}
                    if (mode == 0) {
                        location.href = '/' + loc.slug;
                    } else {
                        input.val(loc.name);
                        locationInput.val(loc.id);
                        popup.hide();
                        currentIndex = -1;
                        defaultLocationId = loc.id;
                    }
                }
                break;
            default:
                var txt = input.val().toLowerCase();
                var matches = [];
                var matchesCount = 0;
                if (txt.length > 0) {
                    // priority to matches that *start* with the search string..
                    $.each(that.locations, function(idx, element) {
                        if (element.name.toLowerCase().indexOf(txt) == 0) {
                            matches.push(element);
                            matchesCount++;
                            if (matchesCount >= 10) {
                                // stop matching
                                return false;
                            }
                        }
                    });
                    // then get mid-string matches up to max 10 matches..
                    if (matchesCount < 10) {
                        $.each(that.locations, function(idx, element) {
                            if (element.name.toLowerCase().indexOf(txt) > 0) {
                                matches.push(element);
                                matchesCount++;
                                if (matchesCount >= 10) {
                                    // stop matching
                                    return false;
                                }
                            }
                        });
                    }
                }
                if (matches.length > 0) {
                    list.empty();
                    currentIndex = -1;
                    $.each(matches, function(idx, element) {
                        var county = that.locations[element.parent_id];
                        var elementname = element.name;
                        if (county != null) {
                            elementname += ' (' + county.name + ')';
                        }
                        var searchpos = elementname.toLowerCase().indexOf(txt, 0);
                        if (searchpos >= 0) {
                            elementname = elementname.substr(0, searchpos) + '<span class="fv-match">' + elementname.substr(searchpos, txt.length) + '</span>' + elementname.substr(searchpos + txt.length);
                        }
                        
                        var $li = $('<li></li>').data('dataId', element.id);
                        if (mode == 1) {
                            $li.html('<a href="#">' + elementname + '</a>')
                            $li.children('a').click(function() {
                                input.val(element.name);
                                locationInput.val(element.id);
                                popup.hide();
                                input.data('currentIndex', -1);
                                input.data('defaultLocationId', element.id);
                                return false;
                            });
                        } else {
                            $li.html('<a href="/' + element.slug + '">' + elementname + '</a>')
                        }
                        if (element.id == selectedId) {
                            $li.addClass('selected');
                            currentIndex = idx;
                        }
                        list.append($li);
                    });
                    popup.show();
                } else {
                    popup.hide();
                    currentIndex = -1;
                }
                break;
            }
            input.data('currentIndex', currentIndex);
            input.data('defaultLocationId', defaultLocationId);
        }
        
    };
})(jQuery);

jQuery(document).ready(jQuery.proxy(minsak.init, minsak));
