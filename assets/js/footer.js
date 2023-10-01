const pins = document.querySelector('#pins')
const footer = document.querySelector('footer')

const tab = document.querySelector('#tab')

pins.addEventListener('click',function()
{
    footer.style.bottom="0";
    
})

tab.addEventListener('click', function(){
    footer.style.bottom="-200%";
    
})