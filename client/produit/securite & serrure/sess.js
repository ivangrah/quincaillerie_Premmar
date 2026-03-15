const image = [
    "wooden-table-with-work-tools-helmet.jpg",
    "2301.i518.011.S.m005.c13.realistic keys keyholes padlocks.jpg"
];

const description = [
    "Securite",
    "Serrure" 
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
