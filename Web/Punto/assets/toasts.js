function addToast(content){
    var toast = document.createElement("div");
    toast.classList.add("toast");
    toast.innerHTML = content;
    document.querySelector(".toast-container").appendChild(toast);
    setTimeout(function(){
        toast.remove();
    }, 3000);
}

export { addToast };
