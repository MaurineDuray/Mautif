const burger = document.querySelector("#burgerMenu")
const menuResponsive = document.querySelector('#slideMenu')

const items = document.querySelectorAll('#slideMenu ul li')
console.log(items)

burger.addEventListener('click', function(){
    let openned = menuResponsive.getAttribute('class')
    if(openned=='open'){
        menuResponsive.classList.remove('open')
        burger.classList.remove('open');
    }else{
        menuResponsive.classList.add('open')
        burger.classList.add('open');
    }
   
})

items.forEach(item=>{
    item.addEventListener('click', function(){
    let openned = menuResponsive.getAttribute('class')
    if(openned=='open'){
        menuResponsive.classList.remove('open')
        burger.classList.remove('open');
    }else{
        menuResponsive.classList.add('open')
        burger.classList.add('open');
    }
})

})
