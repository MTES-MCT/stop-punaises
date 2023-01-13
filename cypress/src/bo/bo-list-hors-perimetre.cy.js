describe('Go to list of hors-perimetre', { testIsolation: false }, () => {
  it ('Displays the list of hors-perimetre', () => {
    cy.get('.fr-nav__list').contains('Hors périmètre').click()
    cy.wait(300)
    cy.get('.fr-container h1').contains('Signalements hors périmètre')
  })

})
