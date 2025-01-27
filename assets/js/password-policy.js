jQuery(function($) {
    var policy = wcPasswordPolicy;
    if (policy.enabled !== 'yes') return;

    // Add validation message container
    $('<div class="password-policy-requirements"></div>').insertAfter('input[name="password_1"]');
    
    function validatePassword(password) {
        var errors = [];
        var requirements = [];

        // Check minimum length
        if (password.length < policy.min_length) {
            errors.push(policy.messages.min_length);
        }
        requirements.push({
            text: policy.messages.min_length,
            met: password.length >= policy.min_length
        });

        // Check uppercase
        if (policy.require_uppercase === 'yes') {
            var hasUppercase = /[A-Z]/.test(password);
            if (!hasUppercase) {
                errors.push(policy.messages.uppercase);
            }
            requirements.push({
                text: policy.messages.uppercase,
                met: hasUppercase
            });
        }

        // Check lowercase
        if (policy.require_lowercase === 'yes') {
            var hasLowercase = /[a-z]/.test(password);
            if (!hasLowercase) {
                errors.push(policy.messages.lowercase);
            }
            requirements.push({
                text: policy.messages.lowercase,
                met: hasLowercase
            });
        }

        // Check numbers
        if (policy.require_numbers === 'yes') {
            var hasNumber = /[0-9]/.test(password);
            if (!hasNumber) {
                errors.push(policy.messages.numbers);
            }
            requirements.push({
                text: policy.messages.numbers,
                met: hasNumber
            });
        }

        // Check special characters
        if (policy.require_special === 'yes') {
            var hasSpecial = /[^A-Za-z0-9]/.test(password);
            if (!hasSpecial) {
                errors.push(policy.messages.special);
            }
            requirements.push({
                text: policy.messages.special,
                met: hasSpecial
            });
        }

        return {
            valid: errors.length === 0,
            errors: errors,
            requirements: requirements
        };
    }

    $('input[name="password_1"]').on('keyup', function() {
        var password = $(this).val();
        var result = validatePassword(password);
        var $requirements = $('.password-policy-requirements');
        
        // Clear previous messages
        $requirements.empty();

        // Display requirements with checkmarks or x marks
        var html = '<ul class="password-requirements-list">';
        result.requirements.forEach(function(req) {
            html += '<li class="' + (req.met ? 'requirement-met' : 'requirement-not-met') + '">';
            html += '<span class="requirement-icon">' + (req.met ? '✓' : '✗') + '</span> ';
            html += req.text;
            html += '</li>';
        });
        html += '</ul>';
        
        $requirements.html(html);

        // Add CSS if not already added
        if (!$('#password-policy-css').length) {
            $('head').append(`
                <style id="password-policy-css">
                    .password-requirements-list {
                        list-style: none;
                        padding: 10px;
                        margin: 10px 0;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        background: #f8f8f8;
                    }
                    .password-requirements-list li {
                        margin-bottom: 5px;
                        padding: 3px 0;
                        font-size: 14px;
                    }
                    .requirement-met {
                        color: #4CAF50;
                    }
                    .requirement-not-met {
                        color: #f44336;
                    }
                    .requirement-icon {
                        display: inline-block;
                        width: 20px;
                        font-weight: bold;
                    }
                    .password-policy-requirements {
                        margin-top: 10px;
                    }
                </style>
            `);
        }
    });

    // Prevent form submission if password is invalid
    $('form.register, form.woocommerce-ResetPassword, form.woocommerce-EditAccountForm').on('submit', function(e) {
        var password = $('input[name="password_1"]').val();
        if (password) {
            var result = validatePassword(password);
            if (!result.valid) {
                e.preventDefault();
                if ($('.woocommerce-error').length === 0) {
                    var errorHtml = '<ul class="woocommerce-error"><li>' + 
                        result.errors.join('</li><li>') + 
                        '</li></ul>';
                    $(this).prepend(errorHtml);
                }
                $('html, body').animate({
                    scrollTop: $('.woocommerce-error').offset().top - 100
                }, 500);
            }
        }
    });
});
