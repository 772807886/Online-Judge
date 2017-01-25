/*
 * Copyright 2017 Liming Jin
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Created by Liming on 2017/1/18.
 */
"use strict";
let editor = null;
let editor_edited = false;
(() => {
    if(document.getElementById('editor')) {
        editor = ace.edit('editor');
        editor.setTheme("ace/theme/monokai");
        editor.setFontSize('16px');
        editor.setOption("vScrollBarAlwaysVisible", true);
        editor.$blockScrolling = Infinity;
        editor.session.on('change', () => {
            editor_edited = true;
        });

        document.getElementById('download').addEventListener('click', () => {
            let a = document.createElement('a');
            let file = new Blob([editor.getValue()], {type: 'plain/text'});
            let event = document.createEvent('MouseEvents');
            event.initEvent('click', false, false);
            a.download = document.title + '.' + constant.language_type[language.value][4];
            a.href = URL.createObjectURL(file);
            a.dispatchEvent(event);
        });

        let callback = () => {
            let language = document.getElementById('language');
            language.innerHTML = '';
            for(let i = 0; i < constant.language_type.length; ++i) {
                let option = document.createElement('option');
                option.value = i;
                option.innerHTML = constant.language_type[i][0];
                language.appendChild(option);
            }
            let change = () => {
                setCookie('_language', language.value, 2592000000);
                if(!editor_edited) {
                    editor.session.setMode(constant.language_type[language.value][1]);
                    editor.setValue(constant.language_type[language.value][2]);
                    editor.gotoLine(constant.language_type[language.value][3]);
                    editor_edited = false;
                }
            };
            let _language = getCookie('_language');
            if(_language) {
                language.value = _language;
            } else {
                language.value = 0;
                setCookie('_language', 0, 2592000000);
            }
            change();
            language.addEventListener('change', change);
        };
        if(constant) {
            callback();
            return;
        }
        ajax('GET', '/j/constant.json', null, (xmlHttp) => {
            try {
                constant = JSON.parse(xmlHttp.responseText);
                callback();
            } catch (e) {
                console.error('Load Constant Failed.');
            }
        });
    }
})();