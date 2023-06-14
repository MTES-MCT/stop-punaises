import user from '../../fixtures/user.json'

describe('Post front signalement check geoloc', { testIsolation: false }, () => {
  it ('Get geoloc information', () => {
    cy.visit('http://localhost:8090/')
    cy.get('#code-postal').type(user.codepostal+user.codepostal)
    cy.get('.btn-next').click()
    cy.wait(1000)
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_typeLogement_1').click()
    cy.get('#signalement_front_superficie').type(45)
    cy.get('#rechercheAdresse').type(user.address)
    cy.wait(300)

    cy.get('#rechercheAdresseListe .fr-mb-1v').first().click()
    cy.wait(300)

    cy.get('#signalement_front_geoloc').should('have.value',user.geoloc)
  })

})
