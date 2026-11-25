/**
 * Formulário de reservas: busca horários disponíveis (AJAX) e envia a reserva sem recarregar a página.
 */
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("form-reserva");
  if (!form) return;

  const campoData = document.getElementById("r-data");
  const campoHorario = document.getElementById("r-horario");
  const campoTelefone = document.getElementById("r-telefone");
  const botao = document.getElementById("r-enviar");
  const alerta = document.getElementById("reserva-alerta");

  function mostrarAlerta(tipo, mensagens) {
    const lista = Array.isArray(mensagens) ? mensagens : [mensagens];
    alerta.innerHTML = "";
    const div = document.createElement("div");
    div.className = "alert alert-" + tipo + " mt-3";
    // textContent evita injetar HTML vindo do servidor
    lista.forEach((m, i) => {
      if (i > 0) div.appendChild(document.createElement("br"));
      div.appendChild(document.createTextNode(m));
    });
    alerta.appendChild(div);
    alerta.scrollIntoView({ behavior: "smooth", block: "center" });
  }

  function definirHorarios(opcoes, textoVazio) {
    campoHorario.innerHTML = "";
    const base = document.createElement("option");
    base.value = "";
    base.textContent = opcoes.length ? "Escolha o horário" : textoVazio;
    campoHorario.appendChild(base);
    opcoes.forEach(({ hora, vagas }) => {
      const op = document.createElement("option");
      op.value = hora;
      op.textContent = hora + " (" + vagas + " vagas)";
      campoHorario.appendChild(op);
    });
    campoHorario.disabled = opcoes.length === 0;
  }

  campoData.addEventListener("change", async () => {
    if (!campoData.value) {
      definirHorarios([], "Escolha a data primeiro");
      return;
    }
    definirHorarios([], "Carregando...");
    try {
      const resp = await fetch("php/horarios.php?data=" + encodeURIComponent(campoData.value));
      const dados = await resp.json();
      definirHorarios(dados.horarios || [], "Sem horários disponíveis nesta data");
    } catch (err) {
      definirHorarios([], "Erro ao carregar horários");
    }
  });

  // Máscara simples de telefone: (11) 91234-5678
  campoTelefone.addEventListener("input", () => {
    let d = campoTelefone.value.replace(/\D/g, "").slice(0, 11);
    if (d.length > 6) {
      const meio = d.length === 11 ? 7 : 6;
      d = "(" + d.slice(0, 2) + ") " + d.slice(2, meio) + "-" + d.slice(meio);
    } else if (d.length > 2) {
      d = "(" + d.slice(0, 2) + ") " + d.slice(2);
    }
    campoTelefone.value = d;
  });

  form.addEventListener("submit", async (ev) => {
    ev.preventDefault();
    botao.disabled = true;
    botao.textContent = "Enviando...";
    try {
      const resp = await fetch("php/reservar.php", { method: "POST", body: new FormData(form) });
      const dados = await resp.json();
      if (dados.ok) {
        mostrarAlerta("success", dados.mensagem);
        form.reset();
        definirHorarios([], "Escolha a data primeiro");
      } else {
        mostrarAlerta("danger", dados.erros || ["Não foi possível enviar a reserva."]);
        if (resp.status === 409) campoData.dispatchEvent(new Event("change")); // atualiza vagas
      }
    } catch (err) {
      mostrarAlerta("danger", "Falha de conexão. Tente novamente.");
    } finally {
      botao.disabled = false;
      botao.textContent = "Solicitar reserva";
    }
  });
});
