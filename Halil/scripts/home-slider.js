let index = 0;
const images = ['home1.webp', 'home2.webp', 'home3.webp', 'home4.webp', 'home5.webp'];
setInterval(() => {
  document.querySelector('.hero').style.backgroundImage = `url('assets/home/${images[index]}')`;
  index = (index + 1) % images.length;
}, 10000);