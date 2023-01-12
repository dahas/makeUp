
$(document).ready(() => {

    auth = (formName, path) => {
        $.ajax({
            type: 'POST',
            url: path,
            data: $('form[name="'+formName+'"]').serialize(),
            success: data => {
                data.segments.forEach(segment => {
                    $('*[data-mod="' + segment.dataMod + '"]').html(segment.html);
                })

                showToast(data.toast[0], data.toast[1]);

                if (formName == 'register' && data.toast[0] == "success") {
                    $(document).trigger("user.registered");
                }
                if (formName == 'signin' && data.toast[0] == "success") {
                    $(document).trigger("user.logged.in");
                }
                if (formName == 'signout') {
                    $(document).trigger("user.logged.out");
                }
                $(document).trigger("user.auth");
            },
            dataType: 'json'
        });
    }

});

