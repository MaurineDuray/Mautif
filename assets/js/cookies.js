console.log('cookies')
const okbtn = document.querySelector(".okbtn")
const cookies = document.querySelector(".cookies")

if (localStorage.getItem('cookies')) {
    cookies.style.display="none"
}else{
    cookies.style.display="flex"
    okbtn.addEventListener('click',()=>{
        console.log('cookies oke')
        cookies.style.display="none"
        localStorage.setItem('cookies',true)
    })
}

