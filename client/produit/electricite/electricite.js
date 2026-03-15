const image = [
    "0044_2446WG.png",
    "rallonge-6-prises-de-courant-2pt-avec-cordon-3m-legrand.jpg",
    "coffret-electrique-alpha-ip66-abs-imo.jpg",
    "1 (3).jpg",
    "th_410724-LEGRAND-1000.jpg"
];

const description = [
    "Fiche et Prises",
    "Rallonge et Câblage",
    "Coffret et Boîtier",
    "Interrupteur et Eclairage",
    "Disjoncteur & Dominos"
];

let index = 0;
const img = document.getElementById("image");
const texte = document.getElementById("texte");
const boutav = document.getElementById("boutav");
const boutar = document.getElementById("boutar");

img.src = image[index];
texte.textContent = description[index];

boutav.addEventListener("click", function(){
    index = (index + 1) % image.length;
    img.src = image[index];
    texte.textContent = description[index];
});

boutar.addEventListener("click", function(){
    index = (index - 1 + image.length) % image.length;
    img.src = image[index];
    texte.textContent = description[index];
});
