const body = document.body

const burger = document.querySelector("#burgerMenu")
const menuResponsive = document.querySelector('#slideMenu')

const items = document.querySelectorAll('#slideMenu ul li')
console.log(items)

// let search = document.querySelector('#search')
// let input = document.querySelector('#research')
// let loupe = document.querySelector('#search #loupe')

let login = document.querySelector('#user')
let accountBlock = document.querySelector('#accountBlock')
console.log(accountBlock)

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

// gestion du menu de connexion
login.addEventListener('click', function(){
    console.log('login click')

    accountBlock.classList.toggle('account')
})



})

// gestion du pop up alerte 
const alert = document.querySelectorAll('.alert')
const okflash = document.querySelectorAll('.okeFlash')
if (alert) {
    setTimeout(alertdelete,25000)
}   
okflash.forEach(e=>{
    e.addEventListener('click',()=>{
        alertdelete()
    })
   
})
function alertdelete(){
    alert.forEach(element => {
        element.style.display= "none"
    });
}