cy.custom = {
  disableSmoothScroll: () => {
    cy.document().then(document => {
      const node = document.createElement('style');
      node.innerHTML = 'html { scroll-behavior: inherit !important; }';
      document.body.appendChild(node);
    });
  }
}
