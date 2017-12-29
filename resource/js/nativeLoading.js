var tempLoadingTimeout = "";
var animetionFinish = "";
loadingInit();
function loadingInit() {
    document.querySelector('head').innerHTML += '' +
        '<style>.loading_cycle {width:100vw;height:100vh;position: fixed;line-height: 100vh;text-align: center;top: 0px;left: 0px;z-index: 9999;background: rgba(0,0,0,0.5); display: none;opacity: 0; transition: all 0.5s;}.loading_cycle_active{opacity: 1; display:block; transition: all 0.5s;}.loader_inner {display: inline-block;border: 10px solid rgba(255,255,255,0.7); border-top: 10px solid #9f9aa1; border-radius: 50%;width: 50px;height: 50px;animation: spin 1.5s linear infinite;} @keyframes spin {0% { transform: rotate(0deg); }100% { transform: rotate(360deg); }}</style>';

    var loading_element = document.createElement("div");
    loading_element.className = "loading_cycle";
    loading_element.id = "loading_cycle";

    var loading_cycle = document.createElement("div");
    loading_cycle.className = "loader_inner";

    loading_element.appendChild(loading_cycle);

    document.body.appendChild(loading_element);


}
function showLoading(timeOut, timeOutCallBack) {
    if (typeof (timeOut) != "undefined" && timeOut > 0 && typeof (timeOutCallBack) == "function") {
        tempLoadingTimeout = setTimeout(function() {
            timeOutCallBack();
        },timeOut);
    }
    var loading_c = document.getElementById("loading_cycle");
    loading_c.style.display = "block";
    animetionFinish = false;
    setTimeout(function(){
        loading_c.className = "loading_cycle loading_cycle_active";
        setTimeout(function() {
            animetionFinish = true;
        },500);
    },100);
}
function hideLoading() {
    console.log(animetionFinish);
    if (animetionFinish == true) {

        var loading_c = document.getElementById("loading_cycle");
        loading_c.className = "loading_cycle";
        setTimeout(function(){
            loading_c.style.display = "none";
        },500);
    } else {
        setTimeout(function(){
            hideLoading();
        },200);
    }
}