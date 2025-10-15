let index = 0;
const images = ['home1.jpg', 'home2.jpg', 'home3.jpg', 'home4.jpg', 'home5.jpg'];
setInterval(() => {
  document.querySelector('.hero').style.backgroundImage = `url('assets/home/${images[index]}')`;
  index = (index + 1) % images.length;
}, 10000);