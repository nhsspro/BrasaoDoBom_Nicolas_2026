document.addEventListener("DOMContentLoaded", () => {

  /* ================================
     SCROLL SUAVE PARA LINKS DO MENU
     ================================ */
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener("click", function (e) {
      if (this.getAttribute("href") === "#") return;
      e.preventDefault();
      const destino = document.querySelector(this.getAttribute("href"));
      if (destino) {
        window.scrollTo({
          top: destino.offsetTop - 70,
          behavior: "smooth"
        });
      }
    });
  });

  /* ================================
     BOTÃO "VOLTAR AO TOPO"
     ================================ */
  const btnTopo = document.createElement("button");
  btnTopo.innerHTML = "▲";
  btnTopo.id = "btnTopo";
  btnTopo.style.cssText = `
    position: fixed;
    bottom: 30px;
    right: 30px;
    display: none;
    background: #d4af37;
    color: #000;
    border: none;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    cursor: pointer;
    font-size: 20px;
    font-weight: bold;
    z-index: 999;
    transition: .3s;
  `;
  document.body.appendChild(btnTopo);

  window.addEventListener("scroll", () => {
    btnTopo.style.display = window.scrollY > 300 ? "block" : "none";
  });

  btnTopo.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  /* ================================
     ANIMAÇÃO DE APARECER (FADE-IN)
     ================================ */
  const elementosAnimados = document.querySelectorAll(".fade-in");

  function mostrarAoRolar() {
    const limite = window.innerHeight * 0.85;

    elementosAnimados.forEach(el => {
      const posicao = el.getBoundingClientRect().top;
      if (posicao < limite) {
        el.classList.add("mostrar");
      }
    });
  }

  window.addEventListener("scroll", mostrarAoRolar);
  mostrarAoRolar();

  /* ================================
     CARROSSEL DE DEPOIMENTOS
     ================================ */
  const depoimentos = document.querySelectorAll("#depoimentos p");
  let indexDepo = 0;

  function trocarDepoimento() {
    depoimentos.forEach((dep, i) => {
      dep.style.display = i === indexDepo ? "block" : "none";
    });
    indexDepo = (indexDepo + 1) % depoimentos.length;
  }

  setInterval(trocarDepoimento, 4000);
  trocarDepoimento();

});
