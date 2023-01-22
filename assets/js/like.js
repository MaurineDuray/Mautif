
const hearts = document.querySelectorAll('.heart')
const like = document.querySelectorAll('.coeur')
console.log(hearts)


hearts.forEach(item => {
    item.addEventListener('click', function(){
        console.log('click')
        let noeud = item.childNodes[0]
        console.log(noeud)

        // Activer ou non le like (rempli le coeur)
        if(noeud.getAttribute('like')=="true"){
            noeud.classList.add('fa-regular')
            noeud.setAttribute('like', 'false')
        }else{
            noeud.classList.add('fa-solid')
            noeud.setAttribute('like', 'true')
        }
       
    })
});