
$(document).ready(() => 
{
    login = (path, name, reload) => {
        $.ajax({
            type: 'POST',
            url: path,
            data: $('form[name="' + name + '"]').serialize(),
            success: data => {
                $('*[data-mod="' + data.segment.dataMod + '"]').html(data.segment.html);
                if (reload) {
                    if (data.payload?.toast) {
                        localStorage.setItem("toast", JSON.stringify(data.payload.toast));
                    }
                    location.reload();
                } else {
                    if (data.payload?.toast) {
                        showToast(data.payload.toast[0], data.payload.toast[1]);
                    }
                    if (data.content) {
                        $('*[data-mod="content"]').html(data.content);
                    }
                }
            },
            dataType: 'json'
        });
    }

    logout = (path, name, reload) => {
        $.ajax({
            type: 'POST',
            url: path,
            data: $('form[name="' + name + '"]').serialize(),
            success: data => {
                $('*[data-mod="' + data.segment.dataMod + '"]').html(data.segment.html);
                if (reload) {
                    if (data.payload?.toast) {
                        localStorage.setItem("toast", JSON.stringify(data.payload.toast));
                    }
                    location.reload();
                } else {
                    if (data.payload?.toast) {
                        showToast(data.payload.toast[0], data.payload.toast[1]);
                    }
                    if (data.content) {
                        $('*[data-mod="content"]').html(data.content);
                    }
                }
            },
            dataType: 'json'
        });
    }

    signup = (path, name, reload) => {
        $.ajax({
            type: 'POST',
            url: path,
            data: $('form[name="' + name + '"]').serialize(),
            success: data => {
                $('*[data-mod="' + data.segment.dataMod + '"]').html(data.segment.html);
                if (reload) {
                    if (data.payload?.toast) {
                        localStorage.setItem("toast", JSON.stringify(data.payload.toast));
                    }
                    location.reload();
                } else {
                    if (data.payload?.toast) {
                        showToast(data.payload.toast[0], data.payload.toast[1]);
                    }
                    if (data.content) {
                        $('*[data-mod="content"]').html(data.content);
                    }
                }
            },
            dataType: 'json'
        });
    }
});

