import './style.css';
import { useState, useEffect } from 'react';
import { useDispatch } from '@wordpress/data';

const BtIpayPayment = ({ gateway, eventRegistration, emitResponse }) => {
	const { createErrorNotice } = useDispatch( 'core/notices' );

    let {onPaymentSetup, onCheckoutFail, onPaymentProcessing, onCheckoutAfterProcessingWithError  } = eventRegistration;
    if (onPaymentSetup === undefined) {
        onPaymentSetup = onPaymentProcessing;
    }

    if (onCheckoutFail === undefined) {
        onCheckoutFail = onCheckoutAfterProcessingWithError;
    }

    const getFirstCard = () => {
        if (gateway.cards && gateway.cards.length) {
            return gateway.cards[0]?.id || null;
        }
        return null;
    }
    const [card, setCard] = useState(getFirstCard());
    const [cof, setCof] = useState(false);
    const [saveNew, setSaveNew] = useState(false);

    useEffect(() => {
        const unsubscribe = onCheckoutFail((props) => {
            return {
                messageContext: 'wc/checkout/payments',
                message: props.processingResponse.paymentDetails.message,
                type: emitResponse.responseTypes.FAIL,
            };
        });
        return () => unsubscribe();
    }, [onCheckoutFail, emitResponse.responseTypes.FAIL]);


    useEffect(() => {
        const unsubscribe = onPaymentSetup(() => {
            let response = {
                type: emitResponse.responseTypes.SUCCESS, meta: {},
            };

            let data = {};
            if (card !== null && card !== undefined && saveNew === false) {
                data.bt_ipay_card_id = card;
            }

            if (cof) {
                data.bt_ipay_save_cards = 'save';
            }

            if (saveNew) {
                data.bt_ipay_use_new_card = 'new';
            }

            response.meta.paymentMethodData = data
            return response;
        });
        return () => unsubscribe();
    }, [onPaymentSetup, emitResponse.responseTypes.SUCCESS, card, cof, saveNew]);

    const areCards = gateway.cards && (gateway.cards.length > 0);

    useEffect(() => {
        if (gateway.notices && gateway.notices.length > 0) {
            gateway.notices.forEach((notice) => {
                if (notice.notice) {
                    createErrorNotice(notice.notice, {
                        context: 'wc/checkout',
                    });
                }
            });
        }
    }, [])
   
    
    return (
        <div className='container'>
            <span className='description'>{gateway.description}</span>
            {gateway.canShowCardsOnFile && (
                <div>
                    {areCards && (
                        <div>
                            <BtIpayToggle label={gateway.newCardLabel} name="new" emit={setSaveNew}/>
                            {!saveNew && <BtIpayCardList gateway={gateway} setCard={setCard}/>}
                        </div>
                    )}
                     { (!areCards || saveNew) && <BtIpayToggle label={gateway.saveCardLabel} name="save" emit={setCof}/>}
                </div>
            )}
        </div>
    );
}
const registerBtIpayMethod = ({ wc, bt_ipay_gateway }) => {
    const { registerPaymentMethod } = wc.wcBlocksRegistry;

    registerPaymentMethod(createOptions(bt_ipay_gateway));
}
const BtIpayCardList = ({gateway: {cards , selectLabel}, setCard}) => {

    const setCardId = (e) => {
        setCard(e.target.value);
    }

    return (
        <div className='bt-ipay-card-form'>
            <label for="bt-ipay-card">{selectLabel}</label>
            <select onChange={setCardId} id="bt-ipay-card">
                {cards.map((card) => (
                    <option key={card.id} value={card.id}>{card.pan} - {card.cardholderName}</option>
                ))}
            </select>
        </div>
    )
}
const BtIpayToggle = ({label, emit, name}) => {
    return (
        <div className='bt-ipay-enable-cof'>
            <input type="checkbox" id={`bt-ipay-${name}`} onChange={(e) => emit(e.target.checked)}/>
            <label htmlFor={`bt-ipay-${name}`}>{label}</label>
        </div>
    )
}
const BtIpayPaymentTitle = ({ gateway }) => {
    return (
        <div className='bt-ipay-payment-title'>
            <span className='title-text'>{gateway.title}</span>
            <img src={gateway.icon} alt={gateway.title} /> 
        </div>
    )
}
const createOptions = (gateway) => {
    return {
        name: gateway.paymentMethodId,
        label: <BtIpayPaymentTitle gateway={gateway} />,
        paymentMethodId: gateway.paymentMethodId,
        edit: <div />,
        canMakePayment: () => true,
        ariaLabel: gateway.title,
        content: <BtIpayPayment gateway={gateway} />
    }
}

registerBtIpayMethod(window)