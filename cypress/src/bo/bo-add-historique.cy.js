import user from '../../fixtures/user.json'

describe('Add a historique', () => {
  it ('Adds one historique', () => {
    cy.get('.liste-signalements .add-button-container .fr-btn').contains('Créer un signalement').click()
    cy.wait(300)
    cy.get('.fr-container h1').contains('Détails de l\'intervention')

    // First tab
    cy.get('#signalement_adresse').type(user.address)
    cy.get('#signalement_codePostal').type(user.codepostal)
    cy.get('#signalement_ville').type(user.city)
    cy.get('#signalement_typeLogement').select('appartement')
    cy.get('#signalement_nomOccupant').type(user.lastname)
    cy.get('#signalement_prenomOccupant').type(user.firstname)

    cy.get('nav.stepper-next').contains('Suivant').click()

    // Second tab
    cy.get('#signalement_typeIntervention').select('traitement')
    cy.get('#signalement_dateIntervention').type('2022-01-01')
    cy.get('#signalement_agent > option').eq(2).then(
      element => cy.get('#signalement_agent').select(element.val())
    )
    cy.get('#signalement_niveauInfestation').select('1')
    cy.get('#signalement_typeTraitement').select('vapeur')
    cy.get('#signalement_nombrePiecesTraitees').type('3')
    cy.get('#signalement_delaiEntreInterventions').type('13')
    cy.get('#signalement_faitVisitePostTraitement_1').siblings('label').click()
    cy.get('#signalement_prixFactureHT').type('1300')
    
    cy.get('.fr-icon-checkbox-circle-line').contains('Valider').click()
  })

})
