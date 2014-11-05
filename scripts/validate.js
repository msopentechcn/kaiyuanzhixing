$(document).ready(function () {
    $("#projsite").val('http://');
    $("#reprou").val('http://');

    $("#capchaimg").bind("click", function(){
        
        $.ajax({
            type: "get",
            url: "getcaptcha.php",
            dataType: "json",  
            data: '',  
            success: function(response){  
                $("#capchaimg").attr("src", response.image_src);
                $("#captchavalue").val(response.code);
            }
        })     
        
    });
});

function validateFields() {
    // Validate Project Name
    if($("#proname").val() == "") {
        $("#wproName").text("必填");
        $("#proname").parent().addClass("has-error");
        $("#proname").parent().removeClass("has-success");
        return false;
    }
    else {
        $("#wproName").text("");
        var proname = $("#proname").val();
        if (proname.length > 100) {
            $("#wproName").text("长度过长");
            return false;
        } else {
            $("#proname").parent().removeClass("has-error");
            $("#proname").parent().addClass("has-success");
        }
    }

    // Validate Project Site
    if ($("#projsite").val() == "") {
        $("#wproSite").text("必填");
        $("#proname").parent().addClass("has-error");
        return false;
    }
    else {
        if (isValidURL($("#projsite").val()) == false) {
            $("#wproSite").text("请输入一个有效的URL");
            $("#proname").parent().addClass("has-warning");
            return false;
        }
        else {
            $("#wproSite").text("");
            $("#proname").parent().addClass("has-success");
        }

        var prosite = $("#projsite").val();
        if(prosite.length > 100) {
            $("#wproSite").text("长度过长");
            $("#proname").parent().addClass("has-warning");
            return false;
        }else {
            $("#wproSite").text("");
            $("#proname").parent().addClass("has-success");
        }
    }

    // Validate Master Repo
    if ($("#reprou").val() == "") {
        $("#wproRepo").text("必填");
        $("#proname").parent().addClass("has-error");
        return false;
    }
    else {
        if (isValidRepoURL($("#reprou").val()) == false) {
            $("#wproRepo").text("请输入一个有效的URL");
            $("#proname").parent().addClass("has-warning");
            return false;
        }
        else {
            $("#wproRepo").text("");
            $("#proname").parent().addClass("has-success");
        }

        var reposite = $("#reprou").val();
        if(reposite.length > 100) {
            $("#wproSite").text("长度过长");
            $("#proname").parent().addClass("has-warning");
            return false;
        }else {
            $("#wproSite").text("");
            $("#proname").parent().addClass("has-success");
        }
    }

    // Validate Verification Code
    var vericode = $("#vericode").val().toLowerCase();
    var captchavalue = $("#captchavalue").val().toLowerCase();
    if(vericode != captchavalue) {
        $("#wproVeri").text("验证码错误");
        $("#proname").parent().addClass("has-error");
        return false;
    }
    else {
        $("#wproVeri").text("");
        $("#proname").parent().addClass("has-success");
    }

    // Validate Checkbox is checked
    if($("#term").is(":checked") == false) {
        return false
    }    

    $('#overlay').css('visibility', 'visible');
    $('#statusdialog').css('visibility', 'visible');

    sendStatusRequest(3000);

    return true;
}

function sendStatusRequest(interval) {
    var sessionID = $("#sessid").val();

    var sendData = {sessionid: sessionID};

    $.ajax({
        type: "POST",
        url: "getstatus.php",
        data: sendData
    })
     .done(function (response) {
         $("#statusinfo").text(response);
     })
     .always(function () {
         setTimeout(sendStatusRequest, interval);
     });
}

function isValidRepoURL(url){
    var repoType = $('#reprotype').val();
    var RegExp = "";
    if (repoType == "github") {
        RegExp = /(http|https|git):\/\/\w+/;
    }
    else {
        RegExp = /(http|https|svn):\/\/\w+/;
    }

    if(RegExp.test(url)){
        return true;
    }else{
        return false;
    }
}

function isValidURL(url) {
    var RegExp = /(http|https):\/\/\w+/;

    if(RegExp.test(url)){
        return true;
    }else{
        return false;
    }
} 