import user from '../../fixtures/user.json'

describe('Post front signalement stopped because logement social', { testIsolation: false }, () => {
  it ('Refuses logement social', () => {
    cy.visit('http://localhost:8090/')
    cy.get('#code-postal').type(user.codepostal)
    cy.get('.btn-next').click()
    cy.wait(1000)
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_typeLogement_1').click()
    cy.get('#signalement_front_superficie').type(45)
    cy.get('.skip-search-address').click()
    cy.get('#signalement_front_adresse').type(user.address)
    cy.get('#signalement_front_codePostal').type(user.codepostal)
    cy.get('#signalement_front_ville').type(user.city)
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_locataire_0').click()
    cy.get('#signalement_front_logementSocial_0').click()
    cy.get('#signalement_front_allocataire_1').click()
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('.if-logement-social').should('be.visible')
  })

})
