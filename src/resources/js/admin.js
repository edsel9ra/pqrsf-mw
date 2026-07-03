document.addEventListener('livewire:navigated', () => {
  const main = document.querySelector('.fi-main');
  if (main) {
    main.style.animation = 'none';
    main.offsetHeight;
    main.style.animation = '';
  }
});

document.addEventListener('livewire:navigate', () => {
  const mainCtn = document.querySelector('.fi-main-ctn');
  if (mainCtn) mainCtn.classList.add('fi-main-ctn-loading');
});

document.addEventListener('livewire:navigated', () => {
  const mainCtn = document.querySelector('.fi-main-ctn');
  if (mainCtn) mainCtn.classList.remove('fi-main-ctn-loading');
});

window.addEventListener('load', () => {
  document.dispatchEvent(new CustomEvent('livewire:navigated'));
});
