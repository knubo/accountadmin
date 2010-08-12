//
//  MainView.h
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright 2010 Knubo Borgen. All rights reserved.
//

#import <UIKit/UIKit.h>
#import <Foundation/Foundation.h>
#import "SetupView.h"

@interface MainView : UIView {
    IBOutlet SetupView *config;
}
- (IBAction)hideConfig:(id)sender;
- (IBAction)showConfig:(id)sender;
@end
