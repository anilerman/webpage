$(document).ready(function(){
    
    (function($) {
        "use strict";

    
    jQuery.validator.addMethod('answercheck', function (value, element) {
        return this.optional(element) || /^\bcat\b$/.test(value)
    }, "type the correct answer -_-");

    // validate contactForm form
    $(function() {
        $('#contactForm').validate({
            rules: {
                name: {
                    required: true,
                    minlength: 2
                },
                subject: {
                    required: true,
                    minlength: 4
                },
                email: {
                    required: true,
                    email: true
                },
                message: {
                    required: true,
                    minlength: 20
                }
            },
            messages: {
                name: {
                    required: "come on, you have a name, don't you?",
                    minlength: "your name must consist of at least 2 characters"
                },
                subject: {
                    required: "come on, you have a subject, don't you?",
                    minlength: "your subject must consist of at least 4 characters"
                },
                email: {
                    required: "no email, no message"
                },
                message: {
                    required: "um...yea, you have to write something to send this form.",
                    minlength: "thats all? really?"
                }
            }
        });

        // AJAX submit
        $('#contactForm').on('submit', function(e) {
            e.preventDefault();
            if ($(this).valid()) {
                $.ajax({
                    type: "POST",
                    url: "send_email.php",
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#responseMessage').html(response);
                        $('#contactForm')[0].reset();
                    },
                    error: function() {
                        $('#responseMessage').html("<div style='color: red; font-weight: bold;'>Bir hata oluştu. Lütfen tekrar deneyin.</div>");
                    }
                });
            }
        });
    })
        
 })(jQuery)
})