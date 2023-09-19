/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
// import Like from './js/like';
import axios from 'axios';



require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');
// start the Stimulus application

document.addEventListener('DOMContentLoaded',()=>{
    // const likeElements = [].slice.call(document.querySelectorAll('a[data-action="like"]'));

    // console.log(likeElements)
    // if(likeElements){
    //     new Like(likeElements);
    // }
    const hearts = document.querySelectorAll('.heart')
    // console.log(hearts)

    hearts.forEach(item=>{
        item.addEventListener('click', (event)=>{
            event.preventDefault()
            const url = item.href;
            // console.log('click :'+item)

            const coeur = item.querySelectorAll('svg')
            item.classList.toggle('blush')
            
            const action = item.getAttribute('data-action')
            // console.log(action)
            if (action == "like") {
                let oldurl = item.href;
                // console.log("old= "+oldurl)
                let newurl= oldurl.replaceAll("/add","/remove")
                // console.log("new= "+newurl)
                item.href=newurl
                item.setAttribute('data-action', 'unlike')
                
                let arr = oldurl.split('/')
                // console.log(arr)
                const number = document.querySelector('#nb'+arr[4])
                var nblike = Number(number.textContent)
                // console.log(nblike)
                var newNbLike = nblike + 1
                number.innerHTML=newNbLike+"  <i class='fa-solid fa-heart'></i>"

            }else{
                let oldurl = item.href;
                // console.log("old= "+oldurl)
                let newurl= oldurl.replaceAll("/remove","/add")
                // console.log("new= "+newurl)
                item.href=newurl
                item.setAttribute('data-action', 'like')

                let arr = oldurl.split('/')
                // console.log(arr)
                const number = document.querySelector('#nb'+arr[4])
                var nblike = Number(number.textContent)
                // console.log(nblike)
                var newNbLike = nblike - 1
                number.innerHTML=newNbLike+"  <i class='fa-solid fa-heart'></i>"
            }
            
            
            
            axios.get(url).then(res=>{
                // console.log(res)
            })
        })
    })
})
