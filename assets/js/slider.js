
const caroussel = document.querySelector('.caroussel')
firstImg = caroussel.querySelectorAll("img")[0]
arrowIcons = document.querySelectorAll(".arrows")


let isDragStart = false, isDragging = false, prevPageX, prevScrollLeft, positionDiff; 



const showHideIcons = () =>{
    let scrollWidth = caroussel.scrollWidth - caroussel.clientWidth;
    arrowIcons[0].style.display = caroussel.scrollLeft == 0 ? "none" : "block"
    arrowIcons[1].style.display = caroussel.scrollLeft == scrollWidth ? "none" : "block"
}

arrowIcons.forEach(icon =>{
    icon.addEventListener("click",() => {
        let firstImgWidth = firstImg.clientWidth + 14;
        caroussel.scrollLeft += icon.id == "left" ? -firstImgWidth : firstImgWidth;
        setTimeout(()=> showHideIcons(),60);
    })
})

const autoSlide = () => {
    if(caroussel.scrollLeft == (caroussel.scrollWidth - caroussel.clientWidth)) return;
    positionDiff = Math.abs(positionDiff);
    let firstImgWidth = firstImg.clientWidth + 14;
    let valDifference = firstImgWidth - positionDiff;
    if(caroussel.scrollLeft > prevScrollLeft){
        return caroussel.scrollLeft += positionDiff > firstImgWidth / 3 ? valDifference : positionDiff;
    }caroussel.scrollLeft -= positionDiff > firstImgWidth / 3 ? valDifference : positionDiff;

    console.log(positionDiff);
}
const dragStart = (e) =>{
    isDragStart = true; 
    prevPageX = e.pageX || e.touched[0].pageX;
    prevScrollLeft = caroussel.scrollLeft;
}
const dragging = (e) => {
    if(!isDragStart) return;
    e.preventDefault();
    isDragging = true;
    caroussel.classList.add("dragging");
    positionDiff = (e.pageX || e.touches[0].pageX) - prevPageX;
    caroussel.scrollLeft = prevScrollLeft - positionDiff;
    showHideIcons();
}
const dragStop = () =>{
    isDragStart = false;
    // caroussel.classList.remove("dragging")
    autoSlide();
}

caroussel.addEventListener("mousedown", dragStart);
caroussel.addEventListener("touchStart", dragStart);

caroussel.addEventListener("mousemove", dragging); 
caroussel.addEventListener("touchmove", dragging); 

caroussel.addEventListener("mouseup", dragStop); 
caroussel.addEventListener("mouseleave", dragStop); 
caroussel.addEventListener("touchend", dragStop); 


