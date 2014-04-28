/*global define:true */
define(function (require) {

  "use strict";

  var $ = require('jquery');
  var cookie = require('jqueryCookie');
  var XVID = require('app/ns');
  var form = require('/js/jquery.form.js');
  var Base64 = require('app/global/base64');
  var utils = require('app/global/utils');

  var gxhr = null;
  var $local = $("input[name=filename]"); 
  var $mbclientUrlElement = $("input[name=mbclienturl]");
  var session = JSON.parse($.cookie('xvid.session'));

  // send form
  $('#uploadFile').bind('click', function(event) {
	  $('#uploadDiv').addClass('formHolderAsync');
      // get file handle
	  var f;
        try {
          if(!$local.get(0).files){
              var fakeName = $local.get(0).value
              fakeName = fakeName.replace(/\\/g, '//')
              fakeName = fakeName.substring(fakeName.lastIndexOf('/') + 1, fakeName.length)
              $local.get(0).files = []
              $local.get(0).files[0] = {name : fakeName}
          } 
          f = $local.get(0).files[0];
        }
        catch (e) {}
        
        if (!f) {
      	  $('#uploadDiv').removeClass('formHolderAsync');
            alert("No file chosen, please choose one");
        }
        
        // construct url
        var mbclientUrl = $mbclientUrlElement.get(0).value; 
        var token = session ? encodeURIComponent(session.key) : '';
        var filename = f.name, url = mbclientUrl+"/files?access_token="+token+"&master_key="+window.name;

        // check for CORS
        var xhr = createCORSRequest("POST", url);
        if (!xhr) {
            alert("TODO: CORS not supported in browser!!");
        }
        else {
          var formData = new FormData();
          formData.append("filename", f);
          
          xhr.onreadystatechange=function(){
              if (xhr.readyState==4 && xhr.status==200){
                 var data = $.parseJSON(xhr.responseText);
                 var dbFileId = data["db_file_id"]
                 location.href = "/upload_success/"+dbFileId;
                } else if (xhr.readyState==4 && xhr.status!=200){
                	var data = $.parseJSON(xhr.responseText);
                	var error_message = data["error_message"]
                	handleError(error_message);
                }
           };

          xhr.onerror = function(e) {
        	  handleError();
        	  
          };

          xhr.send(formData);
        }
        
      
  });
  
  var handleError = function(error_message) {
	  $('#uploadDiv').removeClass('formHolderAsync');
	  alert(error_message);
	 
  }
  
/////////////////////////////////////////////////////////////////////////////

  var createCORSRequest = function(method, url) {
    var xhr = new XMLHttpRequest();
    gxhr = xhr;
    if ("withCredentials" in xhr) {
      // Check if the XMLHttpRequest object has a "withCredentials" property.
      // "withCredentials" only exists on XMLHTTPRequest2 objects.
      xhr.open(method, url, true);
    }
    // else if (typeof XDomainRequest != "undefined") {
    //   // Otherwise, check if XDomainRequest.
    //   // XDomainRequest only exists in IE, and is IE's way of making CORS requests.
    //   xhr = new XDomainRequest();
    //   xhr.open(method, url);
    // }
    else {
      // Otherwise, CORS is not supported by the browser.
      xhr = null;
    }
    return xhr;
  };
  

});