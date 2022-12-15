
$(document).ready(() => {

    let fadeDurMS = 200;

    getAttachedParameter = paramter => {
        let params = $('#helper').attr('src').split("?").pop().split("&");
        let paramsArr = [];
        for (let j = 0; j < params.length; j++) {
            let keyVal = params[j].split("=");
            let key = keyVal[0];
            let val = keyVal[1];
            if (key == paramter) {
                return val;
            }
            paramsArr[key] = val[1];
        }
        return paramsArr;
    }

    let rewriting = getAttachedParameter("rw");

    setRoute = (mod, uri) => {
        if (mod) {
            $(this).blur();
            $('nav li a.active').removeClass('active');
            let state = { path: uri, segment: { dataMod: 'content', html: '' }, title: '', content: '' };
            loadContent(state)
            history.pushState(state, mod, uri);
        }
    }

    loadContent = async state => {
        if (!state) {
            let data = await requestData(rewriting == 1 ? 'index.html' : '?mod=index');
            $('*[data-mod="' + data.segment.dataMod + '"]').html(data.segment.html);
            if (data.content) {
                $('*[data-mod="content"]').html(data.content);
            }
            $(document).prop('title', data.title);
        } else if (state.segment.html == '') {
            let data = await requestData(state.path);
            $('*[data-mod="' + data.segment.dataMod + '"]').html(data.segment.html);
            if (data.content) {
                $('*[data-mod="content"]').html(data.content);
            }
            $(document).prop('title', data.title);
        } else {
            $('*[data-mod="content"]').animate({ opacity: 0 }, fadeDurMS, () => {
                $('*[data-mod="' + state.segment.dataMod + '"]').html(state.segment.html);
                if (state.content) {
                    $('*[data-mod="content"]').html(state.content);
                }
                $(document).prop('title', state.title);
                $('*[data-mod="content"]').animate({ opacity: 1 }, fadeDurMS);
            });
        }
    }

    requestData = async path => {
        $('*[data-mod="content"]').animate({ opacity: 0 }, fadeDurMS);
        let state = {};
        await $.ajax({
            type: 'GET',
            url: rewriting == 1 ? '/json/' + path : path + '&render=json',
            dataType: 'json'
        }).fail(() => {
            state = {
                path: path,
                segments: [],
                title: "Error!",
                content: "Sorry! Something has gone wrong :("
            };
        }).done(data => {
            state = { path: path, segment: data.segment, title: data.title, content: data.content };
            history.replaceState(state, data.module, path);
            if (data.content) {
                $('*[data-mod="content"]').html(data.content);
            }
            $('*[data-mod="content"]').animate({ opacity: 1 }, fadeDurMS);
        });
        return state;
    }

    window.onpopstate = event => {
        loadContent(event.state);
    }

    $("form").submit((event) => {
        submitForm(event.currentTarget.action, event.currentTarget.name);
    });

    submitForm = (path, name, reload) => {
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

    setLanguage = lang => {
        $.ajax({
            type: 'GET',
            url: '?mod=language_selector&task=change&cc=' + lang,
            dataType: 'json'
        }).fail()
            .done(data => {
                console.log(data)
                if (data.result == 1) {
                    location.href = location.href;
                }
            });
    }

    showToast = (tid, msg) => {
        $('#toast-' + tid + ' span.toast-msg').html(msg);
        const toast = new bootstrap.Toast($('#toast-' + tid), { animation: true, delay: 3000 });
        toast.show();
    }

    const tempToast = JSON.parse(localStorage.getItem("toast"));
    if (tempToast) {
        showToast(tempToast[0], tempToast[1]);
        localStorage.removeItem("toast")
    }

});

