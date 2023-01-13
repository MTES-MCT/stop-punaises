describe('Go to a hors perimetre', { testIsolation: false }, () => {
  it ('Displays one hors perimetre', () => {
    cy.get('.liste-signalements-hors-perimetres tr .fr-btn.fr-icon-arrow-right-fill').first().click()
    cy.get('.fr-modal__body .fr-modal__content h1').contains('Signalement #')
  })
  it ('Closes one hors perimetre', () => {
    cy.get('.fr-modal__body .fr-link--close').first().click()
  })

})
