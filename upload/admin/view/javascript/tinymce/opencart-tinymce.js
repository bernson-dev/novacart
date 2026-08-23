const advtemplate_templates = [
  {
    title: 'Quick replies',
    items: [
      {
        title: 'Message received',
        content: '<p dir="ltr">Hey {{Customer.FirstName}}!</p>\n<p dir="ltr">Just a quick note to say we&rsquo;ve received your message, and will get back to you within 48 hours.</p>\n<p dir="ltr">For reference, your ticket number is: {{Ticket.Number}}</p>\n<p dir="ltr">Should you have any questions in the meantime, just reply to this email and it will be attached to this ticket.</p>\n<p><strong>&nbsp;</strong></p>\n<p dir="ltr">Regards,</p>\n<p dir="ltr">{{Agent.FirstName}}</p>'
      },
      {
        title: 'Thanks for the feedback',
        content: '<p dir="ltr">Hi {{Customer.FirstName}},</p>\n<p dir="ltr">We appreciate you taking the time to provide feedback on {{Product.Name}}.</p>\n<p dir="ltr">It sounds like it wasn&rsquo;t able to fully meet your expectations, for which we apologize. Rest assured our team looks at each piece of feedback and uses it to decide what to focus on next with {{Product.Name}}.</p>\n<p dir="ltr"><strong>&nbsp;</strong></p>\n<p dir="ltr">All the best, and let us know if there&rsquo;s anything else we can do to help.</p>\n<p dir="ltr">-{{Agent.FirstName}}</p>'
      },
      {
        title: 'Still working on case',
        content: '<p dir="ltr">Hi {{Customer.FirstName}},</p>\n<p dir="ltr">Just a quick note to let you know we&rsquo;re still working on your case. It&rsquo;s taking a bit longer than we hoped, but we&rsquo;re aiming to get you an answer in the next 48 hours.</p>\n<p dir="ltr">Stay tuned,</p>\n<p dir="ltr">{{Agent.FirstName}}</p>'
      }
    ]
  },
  {
    title: 'Closing tickets',
    items: [
      {
        title: 'Closing ticket',
        content: '<p dir="ltr">Hi {{Customer.FirstName}},</p>\n<p dir="ltr">We haven&rsquo;t heard back from you in over a week, so we have gone ahead and closed your ticket number {{Ticket.Number}}.</p>\n<p dir="ltr">If you&rsquo;re still running into issues, not to worry, just reply to this email and we will re-open your ticket.</p>\n<p><strong>&nbsp;</strong></p>\n<p dir="ltr">All the best,</p>\n<p dir="ltr">{{Agent.FirstName}}</p>'
      },
      {
        title: 'Post-call survey',
        content: '<p dir="ltr">Hey {{Customer.FirstName}}!</p>\n<p dir="ltr">&nbsp;</p>\n<p dir="ltr">How did we do?</p>\n<p dir="ltr">If you have a few moments, we&rsquo;d love you to fill out our post-support survey: {{Survey.Link}}</p>\n<p><strong>&nbsp;</strong></p>\n<p dir="ltr">Thanks in advance!<br>{{Company.Name}} Customer Support</p>'
      }
    ]
  },
  {
    title: 'Product support',
    items: [
      {
        title: 'How to find model number',
        content: '<p dir="ltr">Hi {{Customer.FirstName}},</p>\n<p><strong>&nbsp;</strong></p>\n<p dir="ltr">My name is {{Agent.FirstName}} and I will be glad to assist you today.</p>\n<p dir="ltr">To troubleshoot your issue, we first need your model number, which can be found on the underside of your product beneath the safety warning label.&nbsp;</p>\n<p dir="ltr">It should look something like the following: XX.XXXXX.X</p>\n<p dir="ltr">Once you send it over, I will advise on next steps.</p>\n<p><strong>&nbsp;</strong></p>\n<p dir="ltr">Thanks!</p>\n<p dir="ltr">{{Agent.FirstName}}</p>'
      },
      {
        title: 'Support escalation',
        content: '<p dir="ltr">Hi {{Customer.FirstName}},</p>\n<p dir="ltr">We have escalated your ticket {{Ticket.Number}} to second-level support.</p>\n<p dir="ltr">You should hear back from the new agent on your case, {{NewAgent.FirstName}}, shortly.</p>\n<p><strong>&nbsp;</strong></p>\n<p dir="ltr">Thanks,</p>\n<p dir="ltr">{{Company.Name}} Customer Support</p>'
      }
    ]
  }
];

// Initialize TinyMCE editor
tinymce.init({
  selector : 'textarea[data-toggle="summernote"]',
  language: document.documentElement.lang || 'en',
  promotion : false,
  browser_spellcheck: true,
  contextmenu: false,
  license_key: 'GPL', // Ensure this is appropriate for your usage
  plugins : "advlist anchor autolink autosave charmap code codesample directionality emoticons fullscreen help image importcss insertdatetime link lists media nonbreaking pagebreak preview quickbars save searchreplace supercode table visualblocks visualchars wordcount advtable advtemplate",
  menu : {
    file : {
      title : "File",
      items : "newdocument restoredraft | preview | export print | deleteallconversations"
    },
    edit : {
      title : "Edit",
      items : "undo redo | cut copy paste pastetext | selectall | searchreplace"
    },
    view : {
      title : "View",
      items : "wordcount | code | visualaid visualchars visualblocks | spellchecker | preview fullscreen | showcomments"
    },
    insert : {
      title : "Insert",
      items : "image link media addcomment pageembed codesample inserttable | charmap emoticons hr | pagebreak nonbreaking anchor tableofcontents | insertdatetime | inserttemplate addtemplate"
    },
    format : {
      title : "Format",
      items : "bold italic underline strikethrough superscript subscript codeformat | styles blocks fontfamily fontsize align lineheight | forecolor backcolor | language | removeformat"
    },
    tools : {
      title : "Tools",
      items : "code wordcount"
    },
    table : {
      title : "Table",
      items : "inserttable | cell row column | advtablesort | tableprops deletetable"
    },
    help : {
      title : "Help",
      items : "help"
    }
  },
  removed_menuitems : "code",
  menubar : "file edit view insert format table help custom",
  toolbar : "aidialog aishortcuts supercode fullscreen | undo redo | bold italic underline strikethrough lineheight selectall cut copy paste pastetext | blocks fontfamily fontsize | alignleft aligncenter alignright alignjustify outdent indent numlist bullist ltr rtl forecolor backcolor removeformat | image quickimage media link unlink openlink | preview print | anchor codesample pagebreak charmap emoticons",
  advtemplate_templates,
  /**
   * @param {?} callback
   * @param {?} dataAndEvents
   * @param {Object} item
   * @return {undefined}
   */
  file_picker_callback : function(callback, dataAndEvents, item) {
    $("#modal-image").remove();
    $.ajax({
      url : "index.php?route=common/filemanager&user_token=" + getURLVar("user_token") + "&filetype=" + item.filetype,
      dataType : "html",
      /**
       * @param {string} textStatus
       * @return {undefined}
       */
      success : function(textStatus) {
        $("body").append('<div id="modal-image" class="modal">' + textStatus + "</div>");
        $("#modal-image").modal("show");
        $("#modal-image").css("z-index", "9999");
        $("#modal-image").delegate("a.thumbnail", "click", function(types) {
          types.preventDefault();
          callback($(this).attr("href"), {
            text : ""
          });
          $("#modal-image").modal("hide");
        });
      }
    });
  },
  height : 400,
  image_caption : true,
  image_advtab : true,
  importcss_append : true,
  help_tabs : ["shortcuts", "keyboardnav", "versions"],
  quickbars_selection_toolbar : "bold italic | quicklink h2 h3 blockquote quickimage quicktable",
  noneditable_noneditable_class : "mceNonEditable",
  toolbar_mode : "wrap",
  contextmenu : "link image table",
  codeeditor_themes_pack : "twilight merbivore dawn kuroir",
  codeeditor_wrap_mode : true,
  codeeditor_font_size : 14,
  supercode : {
    theme : "xcode",
    fontSize : 14,
    wrap : true,
    autocomplete : true,
    iconName : "edit-block",
    language : "html",
    shortcut : true,
    dark : false,
    autocomplete : true,
    aceCss : "your custom CSS rules or fonts for ace",
    fontFamily : "Monospace"
  }
});
