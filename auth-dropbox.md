---
layout: page
title: Auth Dropbox
permalink: /auth-dropbox/
---

<input type="text" id="access_token">

<script src="/js/underscore-min.js"></script>
<script>
    if (window.location.hash) {
        var pairs = window.location.hash.substr(1).split('&');
        var result = {};
        _.forEach(pairs, function (pair) {
            pair = pair.split('=');
            result[pair[0]] = decodeURIComponent(pair[1] || '');
        });

        var requiredKeys = [
            'access_token',
            'account_id',
            'state',
            'token_type',
            'uid'
        ];

        var validationResult = _.every(requiredKeys, function (key) {
            return _.has(result, key);
        });

        if (!validationResult) {
            alert('Error code 1');
        } else {
            document.getElementById('access_token').value = result.access_token;
        }
    }
</script>