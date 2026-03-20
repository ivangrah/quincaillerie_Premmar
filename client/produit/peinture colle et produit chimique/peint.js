const image = [
    "paint-tins-brushes.jpg",
    "731.jpg",
    "quality-equipment-with-chemical-cleaning-products-tools-maintenance-swimming-pool-wooden-surface-against-white-background-disinfectant-detergent-cleanser-close-up-front-v.jpg"
 
];

const description = [
    "Peinture",
    "Colle",
    "Produit Chimique"
      
];


const lien = [
    "pcc.html",
    "colle/colle.html",
    "produit chimique/index.html"
  
];


let index = 0;
const img = document.getElementById("image");
const texte = document.getElementById("texte");
const boutav = document.getElementById("boutav");
const boutar = document.getElementById("boutar");
const liens = document.getElementById("liens");

img.src = image[index];
texte.textContent = description[index];
liens.href = lien[index];

boutav.addEventListener("click", function(){
    index = (index + 1) % image.length;
    img.src = image[index];
    texte.textContent = description[index];
    liens.href = lien[index];
});

boutar.addEventListener("click", function(){
    index = (index - 1 + image.length) % image.length;
    img.src = image[index];
    texte.textContent = description[index];
    liens.href = lien[index];
});
