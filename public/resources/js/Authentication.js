
$(document).ready(() => {

    auth = (button, formName, path) => {
        $(button).width($(button).width());
        const initialHTML = $(button).html();
        $(button).html('<i class="fa-solid fa-spinner fa-spin-pulse"></i>');
        $.ajax({
            type: 'POST',
            url: path,
            data: $('form[name="'+formName+'"]').serialize(),
            success: data => {
                showToast(data.toast[0], data.toast[1]);

                if (formName == 'register' && data.authorized) {
                    $(document).trigger("user.registered", [data.context]);
                }
                if (formName == 'signin' && data.authorized) {
                    $(document).trigger("user.logged.in", [data.context]);
                }
                if (formName == 'signout') {
                    $(document).trigger("user.logged.out", [data.context]);
                }
                if (!data.authorized) {
                    $(document).trigger("user.login.failed", [data.context]);
                    $(button).html(initialHTML);
                }
                $(document).trigger("user.auth", [data.context]);
            },
            dataType: 'json'
        });
    }

});

