describe('Go to a signalement', { testIsolation: false }, () => {
  it ('Displays one signalement', () => {
    cy.get('.liste-signalements-usagers tr .fr-btn.fr-icon-arrow-right-fill').first().click()
    cy.wait(300)
    cy.get('.fr-container h1').contains('Signalement #')
  })

})
