const home = document.querySelector('#home')

const feuille1 = document.querySelector('#feuille1')
const feuille2 = document.querySelector('#feuille2')
const feuille3 = document.querySelector('#feuille3')
const feuille4 = document.querySelector('#feuille4')

home.addEventListener('click', function(){
    feuille1.style.marginLeft="-20%"
    feuille2.style.marginRight="-35%"
    feuille3.style.marginLeft="-15%"
    feuille4.style.marginRight="-30%"

})