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
                    required: "Size hitap edebilmemiz için lütfen bir isim giriniz.",
                    minlength: "İsminiz en az 2 karakter uzunluğunda olmalı",
                    color: "red"
                },
                subject: {
                    required: "Lütfen bir konu giriniz.",
                    minlength: "Konu en az 4 karakter uzunluğunda olmalı",
                    color: "red"
                },
                email: {
                    required: "Lütfen geçerli bir email adresi giriniz.",
                    color: "red"
                },
                message: {
                    required: "Lütfen bir mesaj bırakın.",
                    minlength: "Mesajınız en az 20 karakter uzunluğunda olmalı",
                    color: "red"
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