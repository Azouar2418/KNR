export function initializeDeleteModals() {
  const deleteBtns = document.querySelectorAll('[data-toggle="delete"]');

  deleteBtns.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const target = e.currentTarget;
      const link = target.getAttribute("href") || target.href;
      const method = target.getAttribute("data-method") || "DELETE";
      showModal(link, method);
    });
  });

  function showModal(link, method = "DELETE") {
    const modalBackground = createModalElement();
    const modalContent = createModalContent(link, method);

    modalBackground.addEventListener("click", hideModal); // Clicking on the modal background will close the modal

    modalContent.addEventListener("click", (e) => {
      e.stopPropagation(); // Prevent clicks on modal content from bubbling to the background
    });

    modalBackground.appendChild(modalContent);
    document.documentElement.appendChild(modalBackground);
  }

  function createModalElement() {
    const modalBackground = document.createElement("div");
    modalBackground.id = "modal-background";
    modalBackground.className =
      "fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 backdrop-blur-sm cursor-pointer"; // Add cursor-pointer class here
    return modalBackground;
  }

  function createModalContent(link, method = "DELETE") {
    const modalContent = document.createElement("div");
    modalContent.className =
      "mx-3 sm:mx-0 cursor-auto text-sm border-[1px] border-black/10 bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-auto dark:border-white/10 dark:bg-[#09090b]";

    const modalMessage = createModalMessage();
    const modalSubMsg = createModalSubMessage();
    const btnsContainer = createModalButtons(link, method);

    modalContent.appendChild(modalMessage);
    modalContent.appendChild(modalSubMsg);
    modalContent.appendChild(btnsContainer);
    return modalContent;
  }

  function createModalMessage() {
    const modalMessage = document.createElement("h3");
    modalMessage.className = "font-bold text-lg text-gray-800 dark:text-white";
    modalMessage.textContent = "Are you sure you want to delete?";
    return modalMessage;
  }

  function createModalSubMessage() {
    const modalSubMsg = document.createElement("p");
    modalSubMsg.className = "text-gray-400 mt-2";
    modalSubMsg.textContent =
      "This action cannot be undone. This will permanently delete your account and remove your data from our servers.";
    return modalSubMsg;
  }

  function createModalButtons(link, method = "DELETE") {
    const btnsContainer = document.createElement("div");
    btnsContainer.className = "flex gap-3 justify-end mt-6";

    const cancelButton = createCancelButton();
    const primaryButton = createPrimaryButton(link, method);

    btnsContainer.appendChild(primaryButton);
    btnsContainer.appendChild(cancelButton);
    return btnsContainer;
  }

  function createCancelButton() {
    const cancelButton = document.createElement("button");
    cancelButton.type = "button";
    cancelButton.className =
      "px-6 py-2 bg-transparent text-black border-[1px] border-black/10 hover:bg-black/10 text-black rounded-md font-medium dark:border-white/10 dark:hover:bg-white/10 dark:text-white";
    cancelButton.textContent = "Cancel";
    cancelButton.addEventListener("click", hideModal);
    return cancelButton;
  }

  function createPrimaryButton(link, method = "DELETE") {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = link;
    form.style.display = "inline";

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    if (csrf) {
      const csrfInput = document.createElement("input");
      csrfInput.type = "hidden";
      csrfInput.name = "_token";
      csrfInput.value = csrf;
      form.appendChild(csrfInput);
    }

    if (method.toUpperCase() !== "POST") {
      const methodInput = document.createElement("input");
      methodInput.type = "hidden";
      methodInput.name = "_method";
      methodInput.value = method.toUpperCase();
      form.appendChild(methodInput);
    }

    const primaryButton = document.createElement("button");
    primaryButton.type = "submit";
    primaryButton.className =
      "px-6 py-2 bg-[#09090b] hover:bg-[#09090b]/90 text-white rounded-md font-medium dark:bg-white dark:text-black dark:hover:bg-white/90";
    primaryButton.textContent = "Submit";
    primaryButton.addEventListener("click", hideModal);
    form.appendChild(primaryButton);
    return form;
  }

  function hideModal() {
    const modalBackground = document.getElementById("modal-background");
    if (modalBackground) {
      modalBackground.remove();
    }
  }
}
