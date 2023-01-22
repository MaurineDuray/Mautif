const burger = document.querySelector("#burgerMenu")
const menuResponsive = document.querySelector('#slideMenu')

const items = document.querySelectorAll('#slideMenu ul li')
console.log(items)

let search = document.querySelector('#search')
let input = document.querySelector('#research')
let loupe = document.querySelector('#search #loupe')


// gestion du menu burger 
burger.addEventListener('click', function(){
    let openned = menuResponsive.getAttribute('class')
    if(openned=='open'){
        menuResponsive.classList.remove('open')
        burger.classList.remove('open');
        menuResponsive.style.transition="all 1s";
    }else{
        menuResponsive.classList.add('open')
        burger.classList.add('open');
        menuResponsive.style.transition="all 1s";
    }
   
})

// gestion de changement de pages menu 
items.forEach(item=>{
    item.addEventListener('click', function(){
    let openned = menuResponsive.getAttribute('class')
    if(openned=='open'){
        menuResponsive.classList.remove('open')
        burger.classList.remove('open');
        menuResponsive.style.transition="all 1s";
    }else{
        menuResponsive.classList.add('open')
        burger.classList.add('open');
        menuResponsive.style.transition="all 1s";
    }
})

// gestion de la barre de recherche

loupe.addEventListener('click',function(){
    console.log('loupe')

    
    input.classList.toggle('actif')
    menuResponsive.classList.remove('open')
        burger.classList.remove('open');
        menuResponsive.style.transition="all 1s";
    
})

})
