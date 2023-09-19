
// const hearts = document.querySelectorAll('.heart')
// const like = document.querySelectorAll('.coeur')
// console.log(hearts)


// hearts.forEach(item => {
//     item.addEventListener('click', function(){
//         console.log('click')
//         let noeud = item.childNodes[0]
//         console.log(noeud)
//         if(item.getAttribute('unlike')){
//             alert('Vous devez être connectés sur le site pour pouvoir liker les patterns')
//         }else{
//              // Activer ou non le like (rempli le coeur)
//             if(noeud.getAttribute('like')=="true"){
//                 noeud.classList.add('fa-regular')
//                 noeud.setAttribute('like', 'false')
//             }else{
//                 noeud.classList.add('fa-solid')
//                 noeud.setAttribute('like', 'true')
//             }
//         }
       
       
//     })
// });

import axios from "axios";

// const hearts = document.querySelectorAll('.heart')
// console.log(hearts)
// export default class Like{
//     constructor(likeElements){
//         this.likeElements = likeElements;

//         if(this.likeElements){
//             this.init();
//         }
//     }

//     init(){
//         this.likeElements.map(element => {
//             element.addEventListener('click',this.onClick)
//         })
//     }

    

//     onClick(event){
//         event.preventDefault();
//         const url = this.href;
        
        
//         const hearts = document.querySelectorAll('.heart')
//         hearts.forEach(item =>{
//             item.addEventListener('click',()=>{
                
//                 item.classList.add('fa-solid')
//             })
            
//         })

//         axios.get(url).then(res=>{
            
//             console.log("réponse"+res)

//         })
                
        
//     }

    
// }