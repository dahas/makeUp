
$(document).ready(() => {

    login = (path) => {
        $.ajax({
            type: 'POST',
            url: path,
            data: $('form[name="signin"]').serialize(),
            success: data => {
                $('*[data-mod="' + data.segment.dataMod + '"]').html(data.segment.html);
                if (data.payload?.toast) {
                    localStorage.setItem("toast", JSON.stringify(data.payload.toast));
                }
                location.reload();
            },
            dataType: 'json'
        });
    }

    logout = (path) => {
        $.ajax({
            type: 'POST',
            url: path,
            data: $('form[name="signout"]').serialize(),
            success: data => {
                $('*[data-mod="' + data.segment.dataMod + '"]').html(data.segment.html);
                if (data.payload?.toast) {
                    localStorage.setItem("toast", JSON.stringify(data.payload.toast));
                }
                location.reload();
            },
            dataType: 'json'
        });
    }

    signup = (path) => {
        $.ajax({
            type: 'POST',
            url: path,
            data: $('form[name="register"]').serialize(),
            success: data => {
                $('*[data-mod="' + data.segment.dataMod + '"]').html(data.segment.html);
                if (data.payload?.toast) {
                    localStorage.setItem("toast", JSON.stringify(data.payload.toast));
                }
                location.reload();
            },
            dataType: 'json'
        });
    }

});

