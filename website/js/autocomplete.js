(function($) {
  'use strict';
  
  window.EventLinkAutocomplete = {
    init: function(selector, type, options) {
      var defaults = {
        minLength: 2,
        delay: 300,
        type: type || 'all'
      };
      
      var settings = $.extend({}, defaults, options);
      
      $(selector).autocomplete({
        source: function(request, response) {
          $.ajax({
            url: getApiUrl(settings.type),
            dataType: "json",
            data: {
              term: request.term,
              type: settings.type
            },
            success: function(data) {
              response(data);
            },
            error: function() {
              response([]);
            }
          });
        },
        minLength: settings.minLength,
        delay: settings.delay,
        select: settings.select || function(event, ui) {
          return true;
        },
        focus: settings.focus || function(event, ui) {
          return false;
        }
      });
    },
    
    initStatic: function(selector, type, options) {
      var defaults = {
        minLength: 1,
        type: type || 'all'
      };
      
      var settings = $.extend({}, defaults, options);
      
      $(selector).autocomplete({
        source: getApiUrl(settings.type),
        minLength: settings.minLength,
        select: settings.select || function(event, ui) {
          return true;
        },
        focus: settings.focus || function(event, ui) {
          return false;
        }
      });
    }
  };
  
  function getApiUrl(type) {
    var path = window.location.pathname;
    var basePath = '../utils/autocomplete_api.php';
    
    if (path.includes('/website/search/') || path.includes('/search/')) {
      basePath = '../utils/autocomplete_api.php';
    } else if (path.includes('/website/')) {
      basePath = './utils/autocomplete_api.php';
    }
    
    return basePath;
  }
  
})(jQuery);
