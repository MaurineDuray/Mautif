const home = document.querySelector('#home')

const feuille1 = document.querySelector('#feuille1')
const feuille2 = document.querySelector('#feuille2')
const feuille3 = document.querySelector('#feuille3')
const feuille4 = document.querySelector('#feuille4')

const click = document.querySelector('#click')

const more = document.querySelector('#more')

home.addEventListener('click', function(){
    feuille1.style.marginLeft="-20%"
    feuille1.style.transform='rotate(10deg)'

    feuille2.style.marginRight="-45vh"
    feuille2.style.transform='rotate(-10deg)'
    feuille2.style.top="-100px"

    feuille3.style.marginLeft="-15%"
    feuille3.style.transform='rotate(-20deg)'

    feuille4.style.marginRight="-25%"
    feuille4.style.transform='rotate(-20deg)'

    click.style.display="none"
    more.style.display="block"

})