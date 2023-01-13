import entreprise from '../../fixtures/entreprise.json'

describe('Go to Mon entreprise', { testIsolation: false }, () => {
  it ('Displays Mon entreprise', () => {
    cy.get('.fr-header__tools-links').contains('Mon entreprise').click()
    cy.wait(300)
    cy.get('.fiche-entreprise .fr-grid-row').contains(entreprise.login)
  })

})
