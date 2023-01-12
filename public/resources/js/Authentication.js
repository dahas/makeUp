
$(document).ready(() => {

    auth = (button, formName, path) => {
        $(button).width($(button).width());
        $(button).html('<i class="fa-solid fa-spinner fa-spin-pulse"></i>');
        $.ajax({
            type: 'POST',
            url: path,
            data: $('form[name="'+formName+'"]').serialize(),
            success: data => {
                showToast(data.toast[0], data.toast[1]);

                if (formName == 'register' && data.toast[0] == "success") {
                    $(document).trigger("user.registered", [data.context]);
                }
                if (formName == 'signin' && data.toast[0] == "success") {
                    $(document).trigger("user.logged.in", [data.context]);
                }
                if (formName == 'signout') {
                    $(document).trigger("user.logged.out", [data.context]);
                }
                $(document).trigger("user.auth", [data.context]);
            },
            dataType: 'json'
        });
    }

});

