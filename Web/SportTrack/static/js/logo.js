
  var i = 0;
  var mode = true;
  setInterval(() => {
    var element = document.querySelector(".logo");
    var base = 100;
    element.style.left = "calc(" + base + "px + " + i + "px)";
    if(mode) {
      i = i + 100;
    }else{
      i = i - 100;
    }
    if(i > 1400) {
      i = 1400;
      mode = false; 
    }else if(i < 100){
      i = 0;
      mode = true;
    }
  }, 200);