document.addEventListener('deviceready', function() {
    window.submitData = function() {
        var account = document.getElementById("account").value;
        var name = document.getElementById('name').value;
        var email = document.getElementById('email').value;
        var contact = document.getElementById('contact').value;
        var pass = document.getElementById('password').value;

        var http = cordova.plugin.http;
        http.setDataSerializer('json');

        http.post('https://dovetech/ardhi/reg.php', {
            account: account,
            username: name,
            email: email,
            contact: contact,
            key: pass
        }, {
            'Content-Type': 'application/json'
        }, function(response) {
            console.log('Response: ' + JSON.stringify(response.data));
            alert('Data submitted successfully!');
        }, function(response) {
            console.error('Error: ' + JSON.stringify(response.error));
            alert('Failed to submit data.' + JSON.stringify(response.error));
        });
    };
});
