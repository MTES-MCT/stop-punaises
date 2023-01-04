describe('Go to a historique', () => {
  it ('Displays one historique', () => {
    cy.get('.liste-signalements-historique tr .fr-btn.fr-icon-arrow-right-fill').first().click()
    cy.wait(300)
    cy.get('.fr-container h1').contains('Signalement #')
  })

})
