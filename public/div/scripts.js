
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
        $(this).blur();
        $('nav li a.active').removeClass('active');
        let state = { path: uri, segments: [{ html: '', target: 'content' }], title: '' };
        loadContent(state)
        history.pushState(state, mod, uri);
    }

    loadContent = async state => {
        if (!state) {
            let data = await requestData(rewriting == 1 ? 'index.html' : '?mod=index');
            data.segments.forEach(segment => {
                $('*[data-mod="' + segment.target + '"]').html(segment.html);
            });
            $(document).prop('title', data.title);
        } else if (state.segments[0].html == '') {
            let data = await requestData(state.path);
            data.segments.forEach(segment => {
                $('*[data-mod="' + segment.target + '"]').html(segment.html);
            });
            $(document).prop('title', data.title);
        } else {
            $('*[data-mod="content"]').animate({ opacity: 0 }, fadeDurMS, () => {
                state.segments.forEach(segment => {
                    $('*[data-mod="' + segment.target + '"]').html(segment.html);
                });
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
                segments: [{ 
                    html: 'Sorry! Something has gone wrong :(', 
                    target: 'content'
                }], 
                title: "Page not found!" 
            };
        }).done(data => {
            state = { path: path, segments: data.segments, title: data.title };
            history.replaceState(state, data.module, path);
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

    submitForm = (path, name) => {
        $('*[data-mod="content"]').animate({ opacity: 0 }, fadeDurMS);
        $.ajax({
            type: 'POST',
            url: path,
            data: $('form[name="' + name + '"]').serialize(),
            success: data => {
                data.segments.forEach(segment => {
                    $('*[data-mod="'+segment.target+'"]').html(segment.html);
                });
                $('*[data-mod="content"]').animate({ opacity: 1 }, fadeDurMS);
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
});

