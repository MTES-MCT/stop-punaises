describe('Go to list of signalements', { testIsolation: false }, () => {
  it ('Displays the list of signalements', () => {
    cy.get('.fr-nav__list').contains('Signalements').click()
    cy.wait(300)
    cy.get('.fr-container h1').contains('Signalements usagers')
  })

})
